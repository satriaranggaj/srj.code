<?php

namespace Tests\Feature\Admin;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin CRUD validation and form-contract regression.
 *
 * These exist because the admin forms and their FormRequests disagreed with each
 * other in ways no test noticed: `project_type` and `featured` were rejected on
 * every submission, yet the suite stayed green because the factories set those
 * columns directly and bypassed validation entirely.
 *
 * Rule of thumb used throughout: assert on the *request*, not on the model. The
 * bug lives in the gap between the rendered HTML and the validated payload.
 */
class AdminCrudValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    /**
     * A minimal payload that has always been valid, so each test below changes
     * exactly one thing.
     *
     * @return array<string, mixed>
     */
    private function minimalProject(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Lensku',
            'status' => Project::STATUS_LIVE,
            'featured' => '0',
        ], $overrides);
    }

    /*
    |--------------------------------------------------------------------------
    | 1-3  project_type
    |--------------------------------------------------------------------------
    */

    public function test_a_configured_project_type_is_accepted(): void
    {
        $type = config('portfolio.project_types')[0];

        $this->post(route('project.store'), $this->minimalProject(['project_type' => $type]))
            ->assertSessionHasNoErrors();

        $this->assertSame($type, Project::sole()->project_type);
    }

    /**
     * Iterated rather than data-provided: PHPUnit evaluates static data providers
     * before the application boots, so config() is unavailable there.
     */
    public function test_every_configured_project_type_can_be_submitted(): void
    {
        $types = config('portfolio.project_types', []);

        $this->assertNotEmpty($types, 'portfolio.project_types must not be empty');

        foreach ($types as $index => $type) {
            $this->post(route('project.store'), $this->minimalProject([
                'project_type' => $type,
                'title' => 'Type '.$index,
            ]))->assertSessionHasNoErrors("project_type [{$type}] must be accepted");

            $this->assertSame(
                $type,
                Project::where('title', 'Type '.$index)->sole()->project_type,
                "project_type [{$type}] was not stored verbatim"
            );
        }

        $this->assertDatabaseCount('projects', count($types));
    }

    public function test_an_arbitrary_project_type_is_still_rejected(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'project_type' => 'Definitely Not A Real Type',
        ]))->assertSessionHasErrors('project_type');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_project_type_numeric_keys_are_not_accepted_as_types(): void
    {
        // Guards against "fixing" the bug by widening Rule::in() to include keys.
        $this->post(route('project.store'), $this->minimalProject(['project_type' => '0']))
            ->assertSessionHasErrors('project_type');
    }

    public function test_the_select_offers_exactly_the_configured_values(): void
    {
        $html = $this->get(route('project.create'))->assertOk()->getContent();

        foreach (config('portfolio.project_types') as $type) {
            $this->assertStringContainsString(
                'value="'.e($type).'"',
                $html,
                "The project_type select is missing the configured value [{$type}]"
            );
        }
    }

    public function test_project_type_is_rejected_on_update_too(): void
    {
        $project = Project::factory()->create(['project_type' => 'Other']);

        $this->put(route('project.update', $project), $this->minimalProject([
            'project_type' => 'Nonsense',
            'title' => 'Renamed',
        ]))->assertSessionHasErrors('project_type');

        $this->assertSame('Other', $project->fresh()->project_type);
    }

    /*
    |--------------------------------------------------------------------------
    | 4-10  featured
    |--------------------------------------------------------------------------
    */

    public function test_featured_zero_is_accepted(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['featured' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse(Project::sole()->featured);
    }

    public function test_featured_one_is_accepted(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['featured' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Project::sole()->featured);
    }

    /**
     * An unchecked HTML checkbox contributes nothing, so the paired hidden field
     * is the only source of truth. This is the real "unchecked" request shape.
     */
    public function test_unchecked_checkbox_creates_an_unfeatured_project(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['featured' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse(Project::sole()->fresh()->featured);
    }

    /**
     * The value a browser actually sends for a checked box whose `value`
     * attribute never reached the DOM. This is the regression that broke the
     * whole admin form.
     */
    public function test_checked_checkbox_creates_a_featured_project(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['featured' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Project::sole()->fresh()->featured);
    }

    public function test_featured_can_be_turned_off_on_update(): void
    {
        $project = Project::factory()->featured()->create();

        $this->put(route('project.update', $project), $this->minimalProject(['featured' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse($project->fresh()->featured);
    }

    public function test_featured_can_be_turned_on_on_update(): void
    {
        $project = Project::factory()->create(['featured' => false]);

        $this->put(route('project.update', $project), $this->minimalProject(['featured' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertTrue($project->fresh()->featured);
    }

    public function test_a_malformed_featured_value_is_rejected(): void
    {
        foreach (['on', 'yes', 'garbage', 'maybe'] as $bad) {
            $this->post(route('project.store'), $this->minimalProject(['featured' => $bad]))
                ->assertSessionHasErrors('featured');
        }

        $this->assertDatabaseCount('projects', 0);
    }

    /**
     * The edit form must render the control as checked, otherwise opening a
     * featured project and saving an unrelated field silently unfeatures it.
     */
    public function test_edit_form_renders_the_featured_checkbox_as_checked(): void
    {
        $project = Project::factory()->featured()->create();

        $html = $this->get(route('project.edit', $project))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]*name="featured"[^>]*\schecked/i',
            $this->checkboxInputHtml($html, 'featured'),
            'The featured checkbox input is missing its checked attribute'
        );
    }

    public function test_edit_form_renders_the_featured_checkbox_as_unchecked_when_not_featured(): void
    {
        $project = Project::factory()->create(['featured' => false]);

        $html = $this->get(route('project.edit', $project))->assertOk()->getContent();
        $input = $this->checkboxInputHtml($html, 'featured');

        $this->assertStringNotContainsStringIgnoringCase('checked', $input);
    }

    /**
     * @depends test_edit_form_renders_the_featured_checkbox_as_checked
     */
    public function test_editing_a_featured_project_does_not_silently_unfeature_it(): void
    {
        $project = Project::factory()->featured()->create(['title' => 'Original']);

        // Exactly what the browser posts when the operator changes only the title.
        $this->put(route('project.update', $project), $this->minimalProject([
            'title' => 'Renamed',
            'featured' => '1',
        ]))->assertSessionHasNoErrors();

        $this->assertTrue($project->fresh()->featured);
        $this->assertSame('Renamed', $project->fresh()->title);
    }

    /*
    |--------------------------------------------------------------------------
    | 11-13  remove_thumbnail (destructive flag)
    |--------------------------------------------------------------------------
    */

    public function test_unchecked_remove_thumbnail_keeps_the_thumbnail(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/lensku.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/lensku.jpg']);

        $this->put(route('project.update', $project), $this->minimalProject())
            ->assertSessionHasNoErrors();

        $this->assertSame('projects/thumbnails/lensku.jpg', $project->fresh()->thumbnail);
        Storage::disk('public')->assertExists('projects/thumbnails/lensku.jpg');
    }

    public function test_checked_remove_thumbnail_deletes_the_thumbnail(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/lensku.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/lensku.jpg']);

        $this->put(route('project.update', $project), $this->minimalProject(['remove_thumbnail' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull($project->fresh()->thumbnail);
        Storage::disk('public')->assertMissing('projects/thumbnails/lensku.jpg');
    }

    public function test_an_absent_remove_thumbnail_flag_cannot_delete_the_thumbnail(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/lensku.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/lensku.jpg']);

        $payload = $this->minimalProject();
        unset($payload['remove_thumbnail']);

        $this->put(route('project.update', $project), $payload)->assertSessionHasNoErrors();

        $this->assertSame('projects/thumbnails/lensku.jpg', $project->fresh()->thumbnail);
        Storage::disk('public')->assertExists('projects/thumbnails/lensku.jpg');
    }

    public function test_a_malformed_remove_thumbnail_value_cannot_delete_the_thumbnail(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/lensku.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/lensku.jpg']);

        // Rejected by validation, so the controller never runs and nothing is deleted.
        $this->put(route('project.update', $project), $this->minimalProject(['remove_thumbnail' => 'garbage']))
            ->assertSessionHasErrors('remove_thumbnail');

        $this->assertSame('projects/thumbnails/lensku.jpg', $project->fresh()->thumbnail);
        Storage::disk('public')->assertExists('projects/thumbnails/lensku.jpg');
    }

    public function test_a_path_outside_the_managed_directory_is_never_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('shared/og-default.png', 'binary');

        // A hostile or legacy value planted directly in the column.
        $project = Project::factory()->create(['thumbnail' => 'shared/og-default.png']);

        $this->put(route('project.update', $project), $this->minimalProject(['remove_thumbnail' => '1']))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists('shared/og-default.png');
        $this->assertNull($project->fresh()->thumbnail);
    }

    /*
    |--------------------------------------------------------------------------
    | 14-20  project CRUD
    |--------------------------------------------------------------------------
    */

    public function test_a_minimal_valid_project_can_be_created(): void
    {
        $this->post(route('project.store'), [
            'title' => 'Bare Minimum',
            'status' => Project::STATUS_LIVE,
            'featured' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect(route('project.index'));

        $project = Project::sole();
        $this->assertSame('Bare Minimum', $project->title);
        $this->assertSame('bare-minimum', $project->slug, 'A blank slug should be generated from the title');
    }

    public function test_a_full_valid_project_can_be_created(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'title' => 'Full Project',
            'slug' => 'full-project',
            'short_description' => 'Short.',
            'description' => 'Long description.',
            'project_type' => 'AI-Powered Application',
            'tech_stack' => ['Laravel', 'FastAPI'],
            'live_url' => 'https://example.com',
            'github_url' => 'https://github.com/satriaranggaj/example',
            'link' => 'https://legacy.example.com',
            'featured' => '1',
            'sort_order' => 3,
            'problem' => 'Problem.',
            'solution' => 'Solution.',
            'highlights' => ['One', 'Two'],
            'challenges' => 'Challenges.',
            'outcome' => 'Outcome.',
            'role' => 'Full Stack Developer',
            'year' => '2026',
        ]))->assertSessionHasNoErrors();

        $project = Project::sole();
        $this->assertSame('full-project', $project->slug);
        $this->assertSame(['Laravel', 'FastAPI'], $project->tech_stack);
        $this->assertSame(['One', 'Two'], $project->highlights);
        $this->assertSame('2026', $project->year);
        $this->assertSame(3, $project->sort_order);
        $this->assertTrue($project->featured);
        $this->assertSame('AI-Powered Application', $project->project_type);
    }

    public function test_a_project_can_be_updated(): void
    {
        $project = Project::factory()->create(['title' => 'Before']);

        $this->put(route('project.update', $project), $this->minimalProject([
            'title' => 'After',
            'slug' => $project->slug,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('After', $project->fresh()->title);
    }

    public function test_an_invalid_request_preserves_old_input(): void
    {
        $this->from(route('project.create'))
            ->post(route('project.store'), [
                'title' => '',
                'status' => 'nonsense',
                'description' => 'My careful case study text.',
            ])
            ->assertSessionHasErrors(['title', 'status'])
            ->assertSessionHasInput('status', 'nonsense')
            ->assertSessionHasInput('description', 'My careful case study text.');

        // A textarea that drops its slot loses everything the operator typed.
        $this->get(route('project.create'))
            ->assertSee('My careful case study text.')
            ->assertSee('Please fix the following');
    }

    /**
     * The tech stack field is a friendly text box, so a validation failure must
     * not throw away what was typed.
     */
    public function test_an_invalid_request_preserves_the_typed_tech_stack(): void
    {
        $this->from(route('project.create'))->post(route('project.store'), [
            'title' => '',
            'status' => Project::STATUS_LIVE,
            'tech_stack_csv' => 'Laravel, FastAPI',
            'highlights_csv' => "First feature\nSecond feature",
        ])->assertSessionHasErrors('title')
            ->assertSessionHasInput('tech_stack_csv', 'Laravel, FastAPI')
            ->assertSessionHasInput('highlights_csv', "First feature\nSecond feature");

        $html = $this->get(route('project.create'))->getContent();

        $this->assertStringContainsString('Laravel, FastAPI', $html, 'Tech stack must survive a validation failure');
        $this->assertStringContainsString('First feature', $html, 'Highlights must survive a validation failure');
    }

    public function test_slug_uniqueness_is_enforced(): void
    {
        Project::factory()->create(['slug' => 'taken']);

        $this->post(route('project.store'), $this->minimalProject(['slug' => 'taken']))
            ->assertSessionHasErrors('slug');
    }

    public function test_a_blank_slug_is_generated_from_the_title(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'title' => 'My Great Project',
            'slug' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('my-great-project', Project::sole()->slug);
    }

    public function test_an_existing_slug_is_not_regenerated_on_update(): void
    {
        $project = Project::factory()->create(['slug' => 'original-slug', 'title' => 'Original']);

        $this->put(route('project.update', $project), $this->minimalProject([
            'title' => 'A Completely Different Title',
            'slug' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('original-slug', $project->fresh()->slug);
    }

    public function test_colliding_generated_slugs_are_disambiguated(): void
    {
        Project::factory()->create(['slug' => 'duplicate', 'title' => 'Duplicate']);

        $this->post(route('project.store'), $this->minimalProject([
            'title' => 'Duplicate',
            'slug' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('duplicate-2', Project::where('title', 'Duplicate')->latest('id')->first()->slug);
    }

    /*
    |--------------------------------------------------------------------------
    | 21-24  array fields
    |--------------------------------------------------------------------------
    */

    public function test_tech_stack_persists_correctly(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['tech_stack' => ['Laravel', 'FAISS']]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['Laravel', 'FAISS'], Project::sole()->tech_stack);
    }

    public function test_highlights_persist_correctly(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['highlights' => ['A', 'B', 'C']]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['A', 'B', 'C'], Project::sole()->highlights);
    }

    public function test_empty_arrays_are_accepted(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'tech_stack' => [],
            'highlights' => [],
        ]))->assertSessionHasNoErrors();

        $project = Project::sole();
        $this->assertSame([], $project->tech_stack);
        $this->assertSame([], $project->highlights);
    }

    public function test_the_tech_stack_item_limit_is_enforced(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'tech_stack' => array_map(fn ($i) => "tech{$i}", range(1, 31)),
        ]))->assertSessionHasErrors('tech_stack');
    }

    public function test_the_highlights_item_limit_is_enforced(): void
    {
        $this->post(route('project.store'), $this->minimalProject([
            'highlights' => array_map(fn ($i) => "highlight {$i}", range(1, 21)),
        ]))->assertSessionHasErrors('highlights');
    }

    public function test_a_tech_stack_item_that_is_not_a_string_is_rejected(): void
    {
        $this->post(route('project.store'), $this->minimalProject(['tech_stack' => [['nested']]]))
            ->assertSessionHasErrors('tech_stack.0');
    }

    public function test_the_edit_form_repopulates_tech_stack_and_highlights(): void
    {
        $project = Project::factory()->create([
            'tech_stack' => ['Laravel', 'FastAPI'],
            'highlights' => ['First feature', 'Second feature'],
        ]);

        $html = $this->get(route('project.edit', $project))->assertOk()->getContent();

        $this->assertStringContainsString('Laravel, FastAPI', $html);
        $this->assertStringContainsString('First feature', $html);
        $this->assertStringContainsString('Second feature', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | 25-27  uploads
    |--------------------------------------------------------------------------
    */

    public function test_a_thumbnail_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->post(route('project.store'), $this->minimalProject([
            'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 640, 360),
        ]))->assertSessionHasNoErrors();

        $path = Project::sole()->thumbnail;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_an_invalid_thumbnail_is_rejected(): void
    {
        Storage::fake('public');

        $this->post(route('project.store'), $this->minimalProject([
            'thumbnail' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_rejected_thumbnail_leaves_the_existing_one_intact(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/keep.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/keep.jpg']);

        $this->put(route('project.update', $project), $this->minimalProject([
            'thumbnail' => UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('thumbnail');

        $this->assertSame('projects/thumbnails/keep.jpg', $project->fresh()->thumbnail);
        Storage::disk('public')->assertExists('projects/thumbnails/keep.jpg');
    }

    public function test_screenshots_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->post(route('project.store'), $this->minimalProject([
            'screenshots' => [
                UploadedFile::fake()->image('one.jpg', 800, 450),
                UploadedFile::fake()->image('two.jpg', 800, 450),
            ],
        ]))->assertSessionHasNoErrors();

        $this->assertCount(2, Project::sole()->screenshots);
    }

    public function test_replacing_a_thumbnail_deletes_the_old_file_after_a_successful_save(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/thumbnails/old.jpg', 'binary');

        $project = Project::factory()->create(['thumbnail' => 'projects/thumbnails/old.jpg']);

        $this->put(route('project.update', $project), $this->minimalProject([
            'thumbnail' => UploadedFile::fake()->image('new.jpg', 640, 360),
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('projects/thumbnails/old.jpg');
        Storage::disk('public')->assertExists($project->fresh()->thumbnail);
    }

    public function test_screenshot_replacement_removes_only_the_superseded_files(): void
    {
        Storage::fake('public');
        foreach (['projects/screenshots/one.jpg', 'projects/screenshots/two.jpg'] as $p) {
            Storage::disk('public')->put($p, 'binary');
        }

        $project = Project::factory()->create([
            'screenshots' => ['projects/screenshots/one.jpg', 'projects/screenshots/two.jpg'],
        ]);

        $this->put(route('project.update', $project), $this->minimalProject([
            'screenshots' => [UploadedFile::fake()->image('new.jpg', 800, 450)],
        ]))->assertSessionHasNoErrors();

        $this->assertCount(1, $project->fresh()->screenshots);
        Storage::disk('public')->assertMissing('projects/screenshots/one.jpg');
        Storage::disk('public')->assertMissing('projects/screenshots/two.jpg');
    }

    public function test_the_screenshot_count_limit_is_enforced(): void
    {
        Storage::fake('public');

        $this->post(route('project.store'), $this->minimalProject([
            'screenshots' => array_map(
                fn ($i) => UploadedFile::fake()->image("shot{$i}.jpg", 400, 300),
                range(1, 13)
            ),
        ]))->assertSessionHasErrors('screenshots');
    }

    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */

    public function test_a_skill_can_be_created_with_a_configured_category(): void
    {
        $category = Skill::CATEGORIES[0];

        $this->post(route('skill.store'), [
            'name' => 'Laravel',
            'category' => $category,
            'url' => 'https://laravel.com',
            'sort_order' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame($category, Skill::sole()->category);
    }

    /**
     * * Iterated rather than data-provided: static providers run before the app boots.
     */
    public function test_every_configured_skill_category_is_accepted(): void
    {
        foreach (Skill::CATEGORIES as $index => $category) {
            $this->post(route('skill.store'), [
                'name' => 'Skill '.$index,
                'category' => $category,
            ])->assertSessionHasNoErrors("category [{$category}] must be accepted");

            $this->assertSame(
                $category,
                Skill::where('name', 'Skill '.$index)->sole()->category
            );
        }
    }

    public function test_an_invalid_skill_category_is_rejected(): void
    {
        $this->post(route('skill.store'), ['name' => 'X', 'category' => 'Made Up Category'])
            ->assertSessionHasErrors('category');

        $this->assertDatabaseCount('skills', 0);
    }

    public function test_the_skill_select_offers_exactly_the_model_categories(): void
    {
        $html = $this->get(route('skill.create'))->assertOk()->getContent();

        foreach (Skill::CATEGORIES as $category) {
            $this->assertStringContainsString('value="'.e($category).'"', $html);
        }
    }

    public function test_an_invalid_skill_url_is_rejected(): void
    {
        $this->post(route('skill.store'), ['name' => 'X', 'url' => 'not a url'])
            ->assertSessionHasErrors('url');
    }

    public function test_unchecked_remove_image_keeps_the_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('skills-logo/laravel.svg', 'binary');

        $skill = Skill::factory()->create(['image' => 'skills-logo/laravel.svg']);

        $this->put(route('skill.update', $skill), [
            'name' => 'Laravel',
            'category' => 'Backend',
        ])->assertSessionHasNoErrors();

        $this->assertSame('skills-logo/laravel.svg', $skill->fresh()->image);
        Storage::disk('public')->assertExists('skills-logo/laravel.svg');
    }

    public function test_checked_remove_image_deletes_the_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('skills-logo/laravel.svg', 'binary');

        $skill = Skill::factory()->create(['image' => 'skills-logo/laravel.svg']);

        $this->put(route('skill.update', $skill), [
            'name' => 'Laravel',
            'category' => 'Backend',
            'remove_image' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($skill->fresh()->image);
        Storage::disk('public')->assertMissing('skills-logo/laravel.svg');
    }

    public function test_a_malformed_remove_image_value_cannot_delete_the_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('skills-logo/laravel.svg', 'binary');

        $skill = Skill::factory()->create(['image' => 'skills-logo/laravel.svg']);

        $this->put(route('skill.update', $skill), [
            'name' => 'Laravel',
            'category' => 'Backend',
            'remove_image' => 'garbage',
        ])->assertSessionHasErrors('remove_image');

        $this->assertSame('skills-logo/laravel.svg', $skill->fresh()->image);
        Storage::disk('public')->assertExists('skills-logo/laravel.svg');
    }

    public function test_replacing_a_skill_logo_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('skills-logo/old.svg', 'binary');

        $skill = Skill::factory()->create(['image' => 'skills-logo/old.svg']);

        $this->put(route('skill.update', $skill), [
            'name' => 'Laravel',
            'category' => 'Backend',
            'image' => UploadedFile::fake()->image('new.png', 64, 64),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('skills-logo/old.svg');
        Storage::disk('public')->assertExists($skill->fresh()->image);
    }

    public function test_a_skill_can_be_updated(): void
    {
        $skill = Skill::factory()->create(['name' => 'Before']);

        $this->put(route('skill.update', $skill), [
            'name' => 'After',
            'category' => 'Tools',
        ])->assertSessionHasNoErrors();

        $this->assertSame('After', $skill->fresh()->name);
        $this->assertSame('Tools', $skill->fresh()->category);
    }

    /*
    |--------------------------------------------------------------------------
    | Certificates
    |--------------------------------------------------------------------------
    */

    public function test_a_certificate_can_be_created(): void
    {
        $this->post(route('certificate.store'), [
            'title' => 'Backend Development',
            'link' => 'https://credential.example.com/abc',
            'issuer' => 'Example Institute',
            'issued_at' => '2025-06-01',
            'description' => 'A credential.',
            'sort_order' => 2,
        ])->assertSessionHasNoErrors();

        $certificate = Certificate::sole();
        $this->assertSame('Backend Development', $certificate->title);
        $this->assertSame('2025-06-01', $certificate->issued_at->toDateString());
    }

    public function test_an_invalid_certificate_url_is_rejected(): void
    {
        $this->post(route('certificate.store'), ['title' => 'X', 'link' => 'nope'])
            ->assertSessionHasErrors('link');
    }

    public function test_an_invalid_certificate_date_is_rejected(): void
    {
        $this->post(route('certificate.store'), ['title' => 'X', 'issued_at' => 'not-a-date'])
            ->assertSessionHasErrors('issued_at');
    }

    public function test_a_certificate_requires_a_title(): void
    {
        $this->post(route('certificate.store'), ['title' => ''])->assertSessionHasErrors('title');
    }

    public function test_unchecked_remove_image_keeps_the_certificate_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cred.png', 'binary');

        $certificate = Certificate::factory()->create(['image' => 'certificates/cred.png']);

        $this->put(route('certificate.update', $certificate), ['title' => 'Kept'])
            ->assertSessionHasNoErrors();

        $this->assertSame('certificates/cred.png', $certificate->fresh()->image);
        Storage::disk('public')->assertExists('certificates/cred.png');
    }

    public function test_checked_remove_image_deletes_the_certificate_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cred.png', 'binary');

        $certificate = Certificate::factory()->create(['image' => 'certificates/cred.png']);

        $this->put(route('certificate.update', $certificate), [
            'title' => 'Removed',
            'remove_image' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($certificate->fresh()->image);
        Storage::disk('public')->assertMissing('certificates/cred.png');
    }

    public function test_a_malformed_remove_image_value_cannot_delete_the_certificate_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cred.png', 'binary');

        $certificate = Certificate::factory()->create(['image' => 'certificates/cred.png']);

        $this->put(route('certificate.update', $certificate), [
            'title' => 'Untouched',
            'remove_image' => 'garbage',
        ])->assertSessionHasErrors('remove_image');

        $this->assertSame('certificates/cred.png', $certificate->fresh()->image);
        Storage::disk('public')->assertExists('certificates/cred.png');
    }

    public function test_a_certificate_can_be_updated(): void
    {
        $certificate = Certificate::factory()->create(['title' => 'Before']);

        $this->put(route('certificate.update', $certificate), [
            'title' => 'After',
            'issuer' => 'New Issuer',
        ])->assertSessionHasNoErrors();

        $this->assertSame('After', $certificate->fresh()->title);
        $this->assertSame('New Issuer', $certificate->fresh()->issuer);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Every admin endpoint, read and write.
     *
     * The route arguments are filled in from real records: SubstituteBindings runs
     * before the `admin` middleware, so a missing model would 404 before
     * authorization was ever consulted and the test would prove nothing.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function adminEndpointProvider(): array
    {
        return [
            'dashboard' => ['get', 'dashboard'],
            'project index' => ['get', 'project.index'],
            'project create' => ['get', 'project.create'],
            'project store' => ['post', 'project.store'],
            'project edit' => ['get', 'project.edit'],
            'project update' => ['put', 'project.update'],
            'project destroy' => ['delete', 'project.destroy'],
            'skill index' => ['get', 'skill.index'],
            'skill create' => ['get', 'skill.create'],
            'skill store' => ['post', 'skill.store'],
            'skill edit' => ['get', 'skill.edit'],
            'skill update' => ['put', 'skill.update'],
            'skill destroy' => ['delete', 'skill.destroy'],
            'certificate index' => ['get', 'certificate.index'],
            'certificate create' => ['get', 'certificate.create'],
            'certificate store' => ['post', 'certificate.store'],
            'certificate edit' => ['get', 'certificate.edit'],
            'certificate update' => ['put', 'certificate.update'],
            'certificate destroy' => ['delete', 'certificate.destroy'],
            'message index' => ['get', 'message.index'],
            'message read' => ['patch', 'message.read'],
            'message destroy' => ['delete', 'message.destroy'],
        ];
    }

    /**
     * @dataProvider adminEndpointProvider
     */
    public function test_a_guest_cannot_reach_admin_endpoints(string $method, string $routeName): void
    {
        auth()->logout();

        $this->{$method}(route($routeName, $this->routeArguments($routeName)))
            ->assertRedirect(route('login'));
    }

    /**
     * @dataProvider adminEndpointProvider
     */
    public function test_an_authenticated_non_admin_cannot_reach_admin_endpoints(string $method, string $routeName): void
    {
        $this->actingAs(User::factory()->create())
            ->{$method}(route($routeName, $this->routeArguments($routeName)))
            ->assertForbidden();
    }

    /**
     * @dataProvider adminEndpointProvider
     */
    public function test_an_admin_is_allowed_on_every_endpoint(string $method, string $routeName): void
    {
        $status = $this->{$method}(route($routeName, $this->routeArguments($routeName)))->getStatusCode();

        $this->assertNotSame(403, $status, "{$method} {$routeName} denied an admin");
        $this->assertNotSame(404, $status, "{$method} {$routeName} did not resolve for an admin");
        $this->assertLessThan(500, $status, "{$method} {$routeName} errored for an admin");
    }

    /**
     * Records that exist, so implicit model binding resolves before authorization.
     *
     * @return array<int, mixed>
     */
    private function routeArguments(string $routeName): array
    {
        return match (true) {
            str_starts_with($routeName, 'project.') && ! in_array($routeName, ['project.index', 'project.create', 'project.store'], true) => [Project::factory()->create()->id],
            str_starts_with($routeName, 'skill.') && ! in_array($routeName, ['skill.index', 'skill.create', 'skill.store'], true) => [Skill::factory()->create()->id],
            str_starts_with($routeName, 'certificate.') && ! in_array($routeName, ['certificate.index', 'certificate.create', 'certificate.store'], true) => [Certificate::factory()->create()->id],
            str_starts_with($routeName, 'message.') && $routeName !== 'message.index' => [\App\Models\ContactMessage::create([
                'name' => 'Visitor',
                'email' => 'visitor@example.com',
                'message' => 'A message long enough to satisfy validation.',
            ])->id],
            default => [],
        };
    }

    /**
     * The FormRequest::authorize() methods only check that *someone* is logged in.
     * Middleware is what actually enforces is_admin, so prove the middleware is
     * doing the work rather than the request classes.
     */
    public function test_form_request_authorize_alone_does_not_grant_access(): void
    {
        $request = new \App\Http\Requests\Admin\ProjectRequest;
        $request->setUserResolver(fn () => User::factory()->create(['is_admin' => false]));

        $this->assertTrue(
            $request->authorize(),
            'authorize() is intentionally permissive; the admin middleware is the real gate'
        );

        $this->actingAs(User::factory()->create())
            ->post(route('project.store'), ['title' => 'X', 'status' => Project::STATUS_LIVE])
            ->assertForbidden();
    }

    public function test_registration_remains_disabled(): void
    {
        config(['portfolio.allow_registration' => false]);
        auth()->logout();

        $this->get('/register')->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * The rendered <input> element for a checkbox, ignoring the wrapping <label>.
     *
     * The component under test forwards attributes to both elements, so a naive
     * substring search over the whole document cannot tell which element carries
     * `checked` - and that distinction is the whole point.
     */
    private function checkboxInputHtml(string $html, string $name): string
    {
        preg_match_all('/<input\b[^>]*\bname="'.preg_quote($name, '/').'"[^>]*>/i', $html, $matches);

        $inputs = array_values(array_filter($matches[0], fn ($tag) => stripos($tag, 'type="checkbox"') !== false));

        $this->assertCount(1, $inputs, "Expected exactly one checkbox input named [{$name}]");

        return $inputs[0];
    }
}
