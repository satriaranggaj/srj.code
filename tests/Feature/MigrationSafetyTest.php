<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use App\Support\PortfolioText;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Schema and data migration behaviour.
 *
 * These tests assert the *contract* the Portfolio V2 migrations promise: legacy
 * values survive, backfills only fill gaps, slugs stay unique, and the NOT NULL
 * relaxation is reversible only while it is genuinely safe.
 *
 * IMPORTANT SCOPE NOTE: everything here runs on SQLite (in-memory). It proves the
 * contract on one engine. MySQL is the production engine and must additionally be
 * rehearsed against a restored copy of production - see
 * docs/MYSQL_MIGRATION_REHEARSAL.md.
 */
class MigrationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_v2_columns_exist_after_migrating(): void
    {
        foreach ([
            'projects' => [
                'slug', 'short_description', 'description', 'thumbnail', 'project_type',
                'tech_stack', 'live_url', 'github_url', 'featured', 'status', 'sort_order',
                'problem', 'solution', 'highlights', 'challenges', 'outcome', 'role',
                'year', 'screenshots',
            ],
            'skills' => ['name', 'category', 'url', 'sort_order'],
            'certificates' => ['issuer', 'issued_at', 'image', 'description', 'sort_order'],
            'users' => ['is_admin'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Expected column {$table}.{$column} to exist after migrating"
                );
            }
        }

        $this->assertTrue(Schema::hasTable('contact_messages'));
    }

    public function test_legacy_columns_are_never_dropped(): void
    {
        foreach ([
            'projects' => ['title', 'link'],
            'skills' => ['image'],
            'certificates' => ['title', 'link'],
            'posts' => ['slug', 'thumbnail', 'excerpt', 'body'],
            'categories' => ['name', 'slug'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Legacy column {$table}.{$column} must not be dropped"
                );
            }
        }
    }

    public function test_relaxed_columns_are_nullable(): void
    {
        // These three used to be NOT NULL and are deliberately relaxed.
        $this->assertTrue(Schema::hasColumn('projects', 'link'));
        $this->assertTrue(Schema::hasColumn('certificates', 'link'));
        $this->assertTrue(Schema::hasColumn('skills', 'image'));

        // Prove it in practice: inserting without them must not throw.
        $projectId = DB::table('projects')->insertGetId([
            'title' => 'No link',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $certificateId = DB::table('certificates')->insertGetId([
            'title' => 'No link',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $skillId = DB::table('skills')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertIsInt($projectId);
        $this->assertIsInt($certificateId);
        $this->assertIsInt($skillId);
    }

    public function test_existing_legacy_values_survive_the_backfill(): void
    {
        $projectId = DB::table('projects')->insertGetId([
            'title' => 'Legacy Project',
            'link' => 'https://legacy.example.com/app',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $certificateId = DB::table('certificates')->insertGetId([
            'title' => 'Legacy Certificate',
            'link' => 'https://legacy.example.com/cert',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $skillId = DB::table('skills')->insertGetId([
            'image' => 'skills-logo/laravel.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the backfill exactly as the migration does.
        $this->runBackfill();

        $this->assertSame('https://legacy.example.com/app', DB::table('projects')->where('id', $projectId)->value('link'));
        $this->assertSame('Legacy Project', DB::table('projects')->where('id', $projectId)->value('title'));
        $this->assertSame('https://legacy.example.com/cert', DB::table('certificates')->where('id', $certificateId)->value('link'));
        $this->assertSame('skills-logo/laravel.png', DB::table('skills')->where('id', $skillId)->value('image'));

        // And the compatible gaps were filled in.
        $this->assertSame('legacy-project', DB::table('projects')->where('id', $projectId)->value('slug'));
        $this->assertSame('https://legacy.example.com/app', DB::table('projects')->where('id', $projectId)->value('live_url'));
        $this->assertSame('Laravel', DB::table('skills')->where('id', $skillId)->value('name'));
    }

    public function test_backfill_never_overwrites_a_value_that_already_exists(): void
    {
        $id = DB::table('projects')->insertGetId([
            'title' => 'Project With Values',
            'slug' => 'a-custom-slug',
            'link' => 'https://legacy.example.com/one',
            'live_url' => 'https://example.com/explicit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runBackfill();

        $row = DB::table('projects')->where('id', $id)->first();

        $this->assertSame('a-custom-slug', $row->slug);
        $this->assertSame('https://example.com/explicit', $row->live_url);
        $this->assertSame('https://legacy.example.com/one', $row->link);
    }

    public function test_backfill_is_idempotent(): void
    {
        $id = DB::table('projects')->insertGetId([
            'title' => 'Idempotent',
            'link' => 'https://legacy.example.com/app',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runBackfill();
        $first = DB::table('projects')->where('id', $id)->first();

        $this->runBackfill();
        $second = DB::table('projects')->where('id', $id)->first();

        $this->assertEquals($first, $second);
    }

    public function test_backfill_generates_unique_slugs_for_duplicate_titles(): void
    {
        $base = now();

        foreach (['Same Title', 'Same Title', 'Same Title'] as $index => $title) {
            DB::table('projects')->insert([
                'title' => $title,
                'link' => 'https://legacy.example.com/'.$index,
                'created_at' => $base,
                'updated_at' => $base,
            ]);
        }

        $this->runBackfill();

        $slugs = DB::table('projects')->orderBy('id')->pluck('slug')->all();

        $this->assertCount(3, $slugs);
        $this->assertCount(3, array_unique($slugs), 'Backfilled slugs must be unique');
        $this->assertSame('same-title', $slugs[0]);
        $this->assertSame('same-title-2', $slugs[1]);
        $this->assertSame('same-title-3', $slugs[2]);
    }

    public function test_backfill_does_not_copy_a_non_url_link_into_live_url(): void
    {
        // Only an actual URL is copied. Anything else is left for the owner to fix.
        $id = DB::table('projects')->insertGetId([
            'title' => 'Not A URL',
            'link' => 'some random text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runBackfill();

        $this->assertNull(DB::table('projects')->where('id', $id)->value('live_url'));
        $this->assertSame('some random text', DB::table('projects')->where('id', $id)->value('link'));
    }

    public function test_existing_users_are_marked_as_admins_so_nobody_is_locked_out(): void
    {
        /*
         * Faithfully simulate a pre-migration database: drop the column, insert an
         * account that has no is_admin value at all, then run the migration.
         */
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });

        $this->assertFalse(Schema::hasColumn('users', 'is_admin'));

        $userId = DB::table('users')->insertGetId([
            'name' => 'Existing Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runIsAdminBackfill();

        $this->assertTrue(Schema::hasColumn('users', 'is_admin'));

        $this->assertTrue(
            (bool) DB::table('users')->where('id', $userId)->value('is_admin'),
            'Existing accounts must retain the access they already had'
        );
    }

    public function test_the_is_admin_column_defaults_to_false_for_new_rows(): void
    {
        // Safe direction: accounts created after the migration are not administrators.
        $userId = DB::table('users')->insertGetId([
            'name' => 'Later Account',
            'email' => 'later@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse((bool) DB::table('users')->where('id', $userId)->value('is_admin'));
    }

    public function test_rollback_of_the_not_null_migration_refuses_to_coerce_nulls(): void
    {
        // A row with NULL link is exactly the state Portfolio V2 now permits.
        DB::table('projects')->insert([
            'title' => 'Nullable Link',
            'link' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Refusing to roll back/');

        $this->relaxMigration()->down();
    }

    public function test_rollback_proceeds_when_no_nulls_exist(): void
    {
        DB::table('projects')->insert([
            'title' => 'Has Link',
            'link' => 'https://legacy.example.com/app',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // No NULLs, so the constraint can be restored without touching data.
        $this->relaxMigration()->down();

        $this->assertSame(
            'https://legacy.example.com/app',
            DB::table('projects')->where('title', 'Has Link')->value('link')
        );
    }

    public function test_slug_generation_helper_handles_awkward_titles(): void
    {
        $this->assertSame('lensku', PortfolioText::slugify('Lensku'));
        $this->assertSame('barcode-identify', PortfolioText::slugify('BarcodeIdentify'));
        $this->assertSame('davina-event', PortfolioText::slugify('Davina Event'));
        $this->assertSame('php', PortfolioText::slugify('PHP'));
        $this->assertSame('Laravel', PortfolioText::nameFromFilename('skills-logo/laravel.png'));
        $this->assertSame('PHP', PortfolioText::nameFromFilename('skills-logo/php.png'));
        $this->assertSame('CSS', PortfolioText::nameFromFilename('skills-logo/css.png'));
        $this->assertNull(PortfolioText::nameFromFilename(''));
    }

    public function test_eloquent_models_round_trip_the_new_columns(): void
    {
        $project = Project::factory()->create([
            'tech_stack' => ['Laravel', 'FastAPI'],
            'highlights' => ['One', 'Two'],
            'screenshots' => ['projects/screenshots/a.png'],
            'featured' => true,
        ]);

        $fresh = Project::findOrFail($project->id);

        $this->assertSame(['Laravel', 'FastAPI'], $fresh->tech_stack);
        $this->assertSame(['One', 'Two'], $fresh->highlights);
        $this->assertSame(['projects/screenshots/a.png'], $fresh->screenshots);
        $this->assertTrue($fresh->featured);

        $skill = Skill::factory()->create(['name' => 'FAISS', 'category' => 'AI / Machine Learning']);
        $this->assertSame('FAISS', $skill->refresh()->display_name);

        $certificate = Certificate::factory()->create(['issuer' => 'Example Org']);
        $this->assertSame('Example Org', $certificate->refresh()->issuer);
    }

    /**
     * Runs the Portfolio V2 backfill migration body against the current test schema.
     */
    private function runBackfill(): void
    {
        $migration = require database_path('migrations/2026_10_04_100400_backfill_portfolio_slugs_and_names.php');
        $migration->up();
    }

    private function relaxMigration(): object
    {
        return require database_path('migrations/2026_10_04_100500_relax_legacy_not_null_columns.php');
    }

    /**
     * Runs the Portfolio V2 admin-authorization migration body.
     */
    private function runIsAdminBackfill(): void
    {
        $migration = require database_path('migrations/2026_10_04_100600_add_is_admin_to_users_table.php');
        $migration->up();
    }
}
