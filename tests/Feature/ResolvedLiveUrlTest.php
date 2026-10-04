<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The live_url -> link fallback, and the distinction between the two real columns
 * and the derived value the public site renders.
 */
class ResolvedLiveUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolved_url_prefers_the_live_url_column(): void
    {
        $project = Project::factory()->create([
            'live_url' => 'https://example.com/live',
            'link' => 'https://legacy.example.com/old',
        ]);

        $this->assertSame('https://example.com/live', $project->resolved_live_url);
        $this->assertTrue($project->hasResolvedLiveUrl());
    }

    public function test_resolved_url_falls_back_to_the_legacy_link_column(): void
    {
        $project = Project::factory()->create([
            'live_url' => null,
            'link' => 'https://legacy.example.com/old',
        ]);

        $this->assertSame('https://legacy.example.com/old', $project->resolved_live_url);
        $this->assertTrue($project->hasResolvedLiveUrl());
    }

    public function test_resolved_url_is_null_when_both_are_absent(): void
    {
        $project = Project::factory()->create([
            'live_url' => null,
            'link' => null,
        ]);

        $this->assertNull($project->resolved_live_url);
        $this->assertFalse($project->hasResolvedLiveUrl());
    }

    public function test_an_empty_live_url_still_falls_back_to_link(): void
    {
        $project = Project::factory()->create([
            'live_url' => '',
            'link' => 'https://legacy.example.com/old',
        ]);

        $this->assertSame('https://legacy.example.com/old', $project->resolved_live_url);
    }

    public function test_the_real_columns_remain_independently_readable(): void
    {
        // The derived accessor must not shadow either column. A caller that needs the
        // raw live_url has to be able to read it.
        $project = Project::factory()->create([
            'live_url' => 'https://example.com/live',
            'link' => 'https://legacy.example.com/old',
        ]);

        $this->assertSame('https://example.com/live', $project->getAttributes()['live_url']);
        $this->assertSame('https://legacy.example.com/old', $project->getAttributes()['link']);
    }

    public function test_a_legacy_project_renders_its_link_as_the_live_demo(): void
    {
        $project = Project::factory()->create([
            'slug' => 'legacy-row',
            'live_url' => null,
            'link' => 'https://legacy.example.com/app',
        ]);

        $this->get(route('project.show', $project))
            ->assertOk()
            ->assertSee('https://legacy.example.com/app', false);
    }

    public function test_no_live_button_is_rendered_when_neither_column_is_set(): void
    {
        $project = Project::factory()->create([
            'slug' => 'no-urls',
            'live_url' => null,
            'link' => null,
        ]);

        $this->get(route('project.show', $project))
            ->assertOk()
            ->assertDontSee('Live demo');
    }

    public function test_updating_live_url_does_not_clear_the_legacy_link(): void
    {
        $project = Project::factory()->create([
            'link' => 'https://legacy.example.com/old',
            'live_url' => null,
        ]);

        $this->actingAs(\App\Models\User::factory()->admin()->create())
            ->put(route('project.update', $project->id), [
                'title' => $project->title,
                'status' => Project::STATUS_LIVE,
                'live_url' => 'https://example.com/new',
            ])
            ->assertRedirect(route('project.index'));

        $project->refresh();

        $this->assertSame('https://example.com/new', $project->resolved_live_url);

        // The legacy value is still intact underneath.
        $this->assertSame('https://legacy.example.com/old', $project->getAttributes()['link']);
    }
}
