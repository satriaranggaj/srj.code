<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCaseStudyTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_slug_renders_the_case_study(): void
    {
        $project = Project::factory()->create([
            'title' => 'Lensku',
            'slug' => 'lensku',
            'short_description' => 'AI-powered visual SKU retrieval.',
            'description' => "First paragraph.\n\nSecond paragraph.",
            'problem' => 'Matching a product photo to a catalogue entry.',
            'solution' => 'Vision embeddings indexed in FAISS, served over FastAPI.',
            'highlights' => ['Barcode scanning', 'Similarity search'],
            'tech_stack' => ['Laravel', 'Python', 'FastAPI', 'FAISS'],
            'challenges' => 'Keeping recall acceptable as the catalogue grew.',
            'outcome' => 'Deployed on a Linux VPS behind Nginx.',
        ]);

        $response = $this->get(route('project.show', $project));

        $response->assertOk();
        $response->assertSee('Lensku');
        $response->assertSee('AI-powered visual SKU retrieval.');
        $response->assertSee('First paragraph.');
        $response->assertSee('Second paragraph.');
        $response->assertSee('Barcode scanning');
        $response->assertSee('FAISS');
        $response->assertSee('Keeping recall acceptable as the catalogue grew.');
    }

    public function test_case_study_uses_the_thumbnail_as_social_preview(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'thumbnail' => 'projects/thumbnails/lensku.png',
        ]);

        $response = $this->get(route('project.show', $project));

        $response->assertOk();
        $response->assertSee('storage/projects/thumbnails/lensku.png', false);
    }

    public function test_invalid_slug_returns_404(): void
    {
        Project::factory()->create(['slug' => 'lensku']);

        $this->get('/projects/does-not-exist')->assertNotFound();
    }

    public function test_archived_project_case_study_returns_404(): void
    {
        $project = Project::factory()->archived()->create(['slug' => 'old-project']);

        $this->get(route('project.show', $project))->assertNotFound();
    }

    public function test_missing_case_study_content_hides_the_sections(): void
    {
        $project = Project::factory()->create([
            'slug' => 'minimal',
            'description' => null,
            'problem' => null,
            'solution' => null,
            'highlights' => null,
            'challenges' => null,
            'outcome' => null,
            'tech_stack' => null,
        ]);

        $response = $this->get(route('project.show', $project));

        $response->assertOk();
        $response->assertSee('minimal');
        $response->assertDontSee('Key features');
        $response->assertDontSee('Challenges');
        $response->assertDontSee('Outcome');
    }

    public function test_case_study_never_renders_an_iframe(): void
    {
        $project = Project::factory()->create(['slug' => 'lensku']);

        $this->get(route('project.show', $project))->assertDontSee('<iframe', false);
    }

    public function test_absent_urls_produce_no_buttons(): void
    {
        $project = Project::factory()->create([
            'slug' => 'no-links',
            'live_url' => null,
            'link' => null,
            'github_url' => null,
        ]);

        $response = $this->get(route('project.show', $project));

        $response->assertOk();
        $response->assertDontSee('Live demo');
        $response->assertDontSee('Source code');
    }

    public function test_legacy_link_column_is_used_as_the_live_url_fallback(): void
    {
        $project = Project::factory()->create([
            'slug' => 'legacy',
            'live_url' => null,
            'link' => 'https://legacy.example.com/app',
        ]);

        $response = $this->get(route('project.show', $project));

        $response->assertOk();
        $response->assertSee('https://legacy.example.com/app', false);
    }
}
