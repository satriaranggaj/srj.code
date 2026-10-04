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

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_admin_pages_require_authentication(): void
    {
        foreach (['/dashboard', '/project', '/skill', '/certificate', '/messages'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }

    public function test_index_pages_render_for_an_authenticated_user(): void
    {
        Project::factory()->create(['title' => 'Undangly']);
        Certificate::factory()->create(['title' => 'Cloud Practitioner']);

        $this->actingAs($this->admin)
            ->get(route('project.index'))
            ->assertOk()
            ->assertSee('Undangly');

        $this->actingAs($this->admin)->get(route('certificate.index'))
            ->assertOk()
            ->assertSee('Cloud Practitioner');

        $this->actingAs($this->admin)->get(route('skill.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('message.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
    }

    public function test_edit_forms_render(): void
    {
        $project = Project::factory()->create(['slug' => 'lensku']);
        $skill = Skill::factory()->create(['name' => 'Laravel']);
        $certificate = Certificate::factory()->create();

        $this->actingAs($this->admin)->get(route('project.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('skill.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('certificate.create'))->assertOk();

        $this->actingAs($this->admin)->get(route('project.edit', $project->id))
            ->assertOk()
            ->assertSee('lensku');

        $this->actingAs($this->admin)->get(route('skill.edit', $skill->id))->assertOk();
        $this->actingAs($this->admin)->get(route('certificate.edit', $certificate->id))->assertOk();
    }

    public function test_store_creates_a_project_and_generates_a_slug(): void
    {
        $response = $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Davina Event',
            'short_description' => 'Event platform with an admin panel.',
            'status' => Project::STATUS_LIVE,
            'tech_stack' => ['Laravel', 'MySQL'],
            'highlights' => ['Guest list management'],
        ]);

        $response->assertRedirect(route('project.index'));

        $project = Project::firstOrFail();

        $this->assertSame('Davina Event', $project->title);
        $this->assertSame('davina-event', $project->slug);
        $this->assertSame(['Laravel', 'MySQL'], $project->tech_stack);
        $this->assertTrue($project->exists);

        $this->get(route('project.show', $project))->assertOk();
    }

    public function test_an_explicit_slug_is_respected(): void
    {
        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Lensku',
            'slug' => 'lensku-barcode-identify',
            'status' => Project::STATUS_LIVE,
        ]);

        $this->assertSame('lensku-barcode-identify', Project::firstOrFail()->slug);
    }

    public function test_duplicate_slugs_are_made_unique(): void
    {
        Project::factory()->create(['slug' => 'lensku']);

        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Lensku',
            'status' => Project::STATUS_LIVE,
        ]);

        $this->assertSame('lensku-2', Project::where('title', 'Lensku')->latest('id')->firstOrFail()->slug);
    }

    public function test_store_validation_rejects_missing_title_and_bad_urls(): void
    {
        $response = $this->actingAs($this->admin)->from(route('project.create'))->post(route('project.store'), [
            'title' => '',
            'live_url' => 'javascript:alert(1)',
            'github_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['title', 'live_url', 'github_url']);
        $this->assertSame(0, Project::count());
    }

    public function test_thumbnail_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Davina Event',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('thumb.png', 1280, 720),
        ]);

        $project = Project::firstOrFail();

        $this->assertNotNull($project->thumbnail);
        Storage::disk('public')->assertExists($project->thumbnail);
    }

    public function test_replacing_a_thumbnail_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Davina Event',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('first.png', 1280, 720),
        ]);

        $project = Project::firstOrFail();
        $originalPath = $project->thumbnail;

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => 'Davina Event',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('second.png', 1280, 720),
        ]);

        $project->refresh();

        $this->assertNotSame($originalPath, $project->thumbnail);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($project->thumbnail);
    }

    public function test_destroy_deletes_the_project_and_its_thumbnail(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Temporary',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('thumb.png', 1280, 720),
        ]);

        $project = Project::firstOrFail();
        $path = $project->thumbnail;

        $this->actingAs($this->admin)
            ->delete(route('project.destroy', $project->id))
            ->assertRedirect(route('project.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_update_can_switch_the_project_status(): void
    {
        $project = Project::factory()->archived()->create(['slug' => 'lensku']);

        $this->get(route('project.show', $project->slug))->assertNotFound();

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => $project->title,
            'status' => Project::STATUS_LIVE,
        ])->assertRedirect(route('project.index'));

        $this->get(route('project.show', $project->slug))->assertOk();
    }

    public function test_existing_project_data_is_never_overwritten_by_an_empty_update(): void
    {
        $project = Project::factory()->create([
            'title' => 'Undangly',
            'link' => 'https://legacy.example.com',
            'live_url' => null,
            'status' => Project::STATUS_LIVE,
        ]);

        $originalSlug = $project->slug;

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => 'Undangly',
            'status' => Project::STATUS_LIVE,
        ]);

        $project->refresh();

        $this->assertSame('https://legacy.example.com', $project->link);
        $this->assertSame('https://legacy.example.com', $project->liveUrl);
        $this->assertSame($originalSlug, $project->slug);
    }
}
