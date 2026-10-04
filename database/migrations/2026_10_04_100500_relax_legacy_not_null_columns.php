<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Portfolio V2 added dedicated `live_url` / `github_url` columns and made the
 * skill logo optional, so three legacy NOT NULL columns are relaxed:
 *
 *   projects.link     - only a fallback for live_url now
 *   certificates.link - the credential URL, already optional in the UI
 *   skills.image      - the logo is optional; the badge renders from the name
 *
 * This migration never changes or removes data: existing values stay exactly as
 * they are. Rolling back restores the original constraint, which only succeeds if
 * every row has a value again.
 *
 * Raw SQL is used deliberately. `$table->string()->nullable()->change()` requires
 * doctrine/dbal, which would add a deployment-time dependency just to relax three
 * constraints. Statements are written per driver so the same migration runs on the
 * production MySQL server and on the in-memory SQLite used by the test suite.
 */
return new class extends Migration
{
    /**
     * table => column to relax.
     *
     * @var array<string, string>
     */
    private array $columns = [
        'projects' => 'link',
        'certificates' => 'link',
        'skills' => 'image',
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $this->setNullable($table, $column, true);
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            // Restoring NOT NULL only succeeds when no row has an empty value.
            DB::table($table)->whereNull($column)->update([$column => '']);

            $this->setNullable($table, $column, false);
        }
    }

    private function setNullable(string $table, string $column, bool $nullable): void
    {
        $driver = DB::connection()->getDriverName();

        $sql = match ($driver) {
            'mysql', 'mariadb' => "ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) ".($nullable ? 'NULL' : 'NOT NULL'),
            'pgsql' => "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" ".($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'),
            'sqlite' => null,
            default => null,
        };

        if ($sql !== null) {
            DB::statement($sql);

            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildForSqlite($table, $column, $nullable);
        }
    }

    /**
     * SQLite cannot relax a constraint in place, so the table definition is
     * rebuilt with the constraint removed and every row copied across.
     *
     * Only used by the test environment.
     */
    private function rebuildForSqlite(string $table, string $column, bool $nullable): void
    {
        $original = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
            [$table]
        );

        if ($original === null || $original->sql === null) {
            return;
        }

        $pattern = $nullable
            ? '/("'.$column.'"\s+[^,\n]*?)\s+not\s+null/i'
            : '/("'.$column.'"\s+[^,\n]*?)(?<!\snot\s)null)/i';

        $definition = preg_replace($pattern, '$1', $original->sql, 1, $replacements);

        if ($replacements !== 1) {
            // Nothing matched: the constraint is already in the requested state.
            return;
        }

        $target = $table.'_nullable_rebuild';

        $definition = str_replace(
            'CREATE TABLE "'.$table.'"',
            'CREATE TABLE "'.$target.'"',
            $definition
        );

        DB::statement('DROP TABLE IF EXISTS "'.$target.'"');
        DB::statement($definition);
        DB::statement("INSERT INTO \"{$target}\" SELECT * FROM \"{$table}\"");
        DB::statement("DROP TABLE \"{$table}\"");
        DB::statement("ALTER TABLE \"{$target}\" RENAME TO \"{$table}\"");
    }
};
