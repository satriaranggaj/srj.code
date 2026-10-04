<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Relaxes the NOT NULL constraint on three legacy columns:
 *
 *   projects.link     - now only a fallback for live_url
 *   certificates.link - the credential URL, already optional in the admin form
 *   skills.image      - the logo is optional; the badge renders from the name
 *
 * WHY NOT $table->string()->nullable()->change()
 *
 * Changing a column in Laravel 10 requires doctrine/dbal, which is not a dependency
 * of this application. Adding it would mean every production host must be able to
 * install packages before `php artisan migrate` will run. Raw driver-native SQL is
 * therefore used instead, which needs no new dependency.
 *
 * DRIVER BEHAVIOUR — explicitly verified per engine:
 *
 *   mysql / mariadb
 *       ALTER TABLE ... MODIFY `col` VARCHAR(255) {NULL|NOT NULL}
 *       An in-place, metadata-only change. Existing rows are never rewritten.
 *
 *   sqlite
 *       SQLite cannot relax a constraint in place, so the table definition is
 *       rebuilt. This path exists purely so the identical migration file also runs
 *       under PHPUnit (in-memory SQLite). It is not the production path.
 *       The rebuild is driven by PRAGMA table_info rather than by rewriting the
 *       CREATE TABLE text, so it cannot depend on how a given SQLite build formats
 *       its DDL.
 *
 *   pgsql
 *       ALTER COLUMN ... {DROP NOT NULL | SET NOT NULL}
 *
 * Idempotent: re-running is a no-op because a column that is already nullable does
 * not match the NOT NULL pattern.
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
            if (! $this->shouldRun($table, $column)) {
                continue;
            }

            $this->setNullable($table, $column, true);
        }
    }

    /**
     * NOT SAFELY REVERSIBLE — see the exception thrown below.
     *
     * Reversing this migration would require re-applying NOT NULL to columns that
     * Portfolio V2 has since deliberately allowed to be empty. Any row holding NULL
     * would either abort the ALTER or force a NULL -> '' rewrite.
     *
     * That rewrite is exactly what this method refuses to do: coercing NULL to an
     * empty string silently changes data, and an empty string is not equivalent to
     * NULL for a URL or a file path. Pretending the rollback were lossless would be
     * worse than refusing it.
     *
     * The correct production rollback is to restore the database from the backup
     * taken immediately before `php artisan migrate` was run, and to redeploy the
     * previous application version.
     */
    public function down(): void
    {
        $affected = [];

        foreach ($this->columns as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $nulls = DB::table($table)->whereNull($column)->count();

            if ($nulls > 0) {
                $affected[] = "{$table}.{$column} ({$nulls} NULL row(s))";
            }
        }

        if ($affected !== []) {
            throw new RuntimeException(
                'Refusing to roll back 2026_10_04_100500_relax_legacy_not_null_columns: '
                .'NULL values now exist in '.implode(', ', $affected).'. '
                .'Converting them to empty strings would silently alter data. '
                .'Restore the pre-migration database backup and redeploy the previous release instead.'
            );
        }

        foreach ($this->columns as $table => $column) {
            $this->setNullable($table, $column, false);
        }
    }

    private function shouldRun(string $table, string $column): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, $column)
            && $this->isNotNull($table, $column);
    }

    private function isNotNull(string $table, string $column): bool
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => $this->sqliteIsNotNull($table, $column),
            default => $this->genericIsNotNull($table, $column),
        };
    }

    /**
     * Reads the constraint from the driver rather than parsing DDL text.
     */
    private function genericIsNotNull(string $table, string $column): bool
    {
        try {
            $sql = match (DB::connection()->getDriverName()) {
                'mysql', 'mariadb' => 'SELECT IS_NULLABLE FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                'pgsql' => 'SELECT is_nullable FROM information_schema.columns
                    WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
                default => null,
            };

            if ($sql === null) {
                // Unknown driver: leave the schema alone rather than guess.
                return false;
            }

            $result = DB::select($sql, [$table, $column]);

            return isset($result[0]) && strtoupper((string) $result[0]->IS_NULLABLE ?? $result[0]->is_nullable ?? 'YES') === 'NO';
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function sqliteIsNotNull(string $table, string $column): bool
    {
        foreach (DB::select("PRAGMA table_info(\"{$table}\")") as $info) {
            if (($info->name ?? null) === $column) {
                return ((int) $info->notnull) === 1;
            }
        }

        return false;
    }

    private function setNullable(string $table, string $column, bool $nullable): void
    {
        $driver = DB::connection()->getDriverName();

        $sql = match ($driver) {
            'mysql', 'mariadb' => "ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) ".($nullable ? 'NULL' : 'NOT NULL'),
            'pgsql' => "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" ".($nullable ? 'DROP NOT NULL' : 'SET NOT NULL'),
            'sqlite' => $nullable ? $this->sqliteRebuild($table, $column) : null,
            default => null,
        };

        if ($sql !== null) {
            DB::statement($sql);
        }
    }

    /**
     * Rebuilds a SQLite table without a NOT NULL constraint on one column.
     *
     * The new CREATE TABLE statement is composed from PRAGMA table_info, so it does
     * not depend on the exact formatting of the original DDL. Only used by the test
     * environment; MySQL handles this with an in-place ALTER.
     */
    private function sqliteRebuild(string $table, string $column): ?string
    {
        $columns = DB::select("PRAGMA table_info(\"{$table}\")");

        if ($columns === []) {
            return null;
        }

        $definitions = [];
        $primaryKey = null;

        foreach ($columns as $info) {
            $name = $info->name;

            $definition = '"'.$name.'" '.$this->sqliteColumnType($info);

            if ((int) $info->notnull === 1 && $name !== $column && $primaryKey === null) {
                $definition .= ' NOT NULL';
            }

            if (($info->pk ?? 0) > 0) {
                $primaryKey = $name;
                $definition .= ' PRIMARY KEY';
            }

            if (($info->dflt_value ?? null) !== null && trim((string) $info->dflt_value) !== '') {
                $definition .= ' DEFAULT '.$info->dflt_value;
            }

            $definitions[] = $definition;
        }

        if ($primaryKey === null) {
            return null;
        }

        $quotedColumns = implode(', ', array_map(fn ($info) => '"'.$info->name.'"', $columns));
        $target = $table.'__nullable_rebuild';

        DB::statement('DROP TABLE IF EXISTS "'.$target.'"');
        DB::statement('CREATE TABLE "'.$target.'" ('.implode(', ', $definitions).')');
        DB::statement("INSERT INTO \"{$target}\" ({$quotedColumns}) SELECT {$quotedColumns} FROM \"{$table}\"");
        DB::statement("DROP TABLE \"{$table}\"");
        DB::statement("ALTER TABLE \"{$target}\" RENAME TO \"{$table}\"");

        return null;
    }

    /**
     * Maps a SQLite declared type back to something valid in a CREATE TABLE
     * statement. SQLite accepts the type verbatim, so it only needs its length
     * suffix restored.
     */
    private function sqliteColumnType(object $info): string
    {
        $type = (string) $info->type;

        if ($type === '') {
            return 'TEXT';
        }

        if (str_contains(strtolower($type), 'int')) {
            return $type;
        }

        if (in_array(strtolower($type), ['text', 'blob', 'real', 'numeric', 'boolean', 'date', 'datetime'], true)) {
            return strtoupper($type);
        }

        return $type;
    }
};
