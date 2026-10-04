<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public visibility rules.
 *
 *   live         -> public everywhere
 *   in_progress  -> public everywhere, shown with its status badge
 *   archived     -> withdrawn from every public surface
 */
class ProjectVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_projects_are_publicly_visible(): void
    {
        $project = Project::factory()->create(['title' => 'Undangly', 'status' => Project::STATUS_LIVE]);

        $this->assertTrue($project->isPubliclyVisible());
        $this->assertContains($project->id, Project::publiclyVisible()->pluck('id')->all());
    }

    public function test_in_progress_projects_are_publicly_visible(): void
    {
        $project = Project::factory()->create([
            'title' => 'Davina Event',
            'status' => Project::STATUS_IN_PROGRESS,
        ]);

        $this->assertTrue($project->isPubliclyVisible());
        $this->assertContains($project->id, Project::publiclyVisible()->pluck('id')->all());

        // Explicitly not treated as archived.
        $this->assertContains($project->id, Project::notArchived()->pluck('id')->all());

        $this->get(route('project'))->assertOk()->assertSee('Davina Event');
    }

    public function test_archived_projects_are_withdrawn_from_every_public_surface(): void
    {
        $project = Project::factory()->archived()->create([
            'title' => 'Retired Thing',
            'description' => 'A case study that exists but is withdrawn.',
        ]);

        $this->assertFalse($project->isPubliclyVisible());
        $this->assertNotContains($project->id, Project::publiclyVisible()->pluck('id')->all());

        $this->get(route('project'))->assertOk()->assertDontSee('Retired Thing');
        $this->get(route('home'))->assertOk()->assertDontSee('Retired Thing');
        $this->get(route('project.show', $project->slug))->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee($project->slug, false);
    }

    public function test_archived_projects_are_still_visible_in_the_admin_listing(): void
    {
        Project::factory()->archived()->create(['title' => 'Retired Thing']);

        $this->actingAs(\App\Models\User::factory()->admin()->create())
            ->get(route('project.index'))
            ->assertOk()
            ->assertSee('Retired Thing');
    }

    public function test_archived_projects_are_never_shown_as_featured_on_the_homepage(): void
    {
        Project::factory()->archived()->featured()->create([
            'title' => 'Archived Featured',
            'description' => 'Withdrawn work.',
        ]);

        Project::factory()->featured()->create([
            'title' => 'Live Featured',
            'description' => 'Published work.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Live Featured');
        $response->assertDontSee('Archived Featured');
    }

    public function test_an_unknown_status_is_treated_as_withdrawn(): void
    {
        $project = Project::factory()->create([
            'title' => 'Weird Status',
            'status' => 'something_unexpected',
        ]);

        // Defensive: only the two known public statuses are treated as public.
        $this->assertFalse($project->isPubliclyVisible());
        $this->get(route('project'))->assertOk()->assertDontSee('Weird Status');
    }

    public function test_deprecated_published_scope_still_behaves_like_publicly_visible(): void
    {
        $live = Project::factory()->create(['status' => Project::STATUS_LIVE]);
        $archived = Project::factory()->archived()->create();

        $ids = Project::published()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($archived->id, $ids);
    }
}
