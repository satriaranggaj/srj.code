<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function publicRouteProvider(): array
    {
        return [
            'home' => ['/', 'home'],
            'about' => ['/about', 'about'],
            'projects' => ['/projects', 'project'],
            'certificates' => ['/certificates', 'certificate'],
            'contact' => ['/contact', 'contact'],
        ];
    }

    /**
     * @dataProvider publicRouteProvider
     */
    public function test_public_pages_return_200(string $uri, string $routeName): void
    {
        $this->get($uri)->assertOk();
    }

    /**
     * @dataProvider publicRouteProvider
     */
    public function test_public_pages_render_core_chrome(string $uri, string $routeName): void
    {
        $response = $this->get($uri);

        $response->assertSee('<html lang="en"', false);
        $response->assertSee('Skip to content', false);
        $response->assertSee(config('portfolio.name'), false);
    }

    /**
     * @dataProvider publicRouteProvider
     */
    public function test_public_pages_expose_seo_metadata(string $uri, string $routeName): void
    {
        $response = $this->get($uri);

        $response->assertSee('<meta name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
    }

    public function test_homepage_renders_with_content(): void
    {
        Project::factory()->featured()->create([
            'title' => 'Lensku',
            'short_description' => 'Visual SKU retrieval application.',
            'tech_stack' => ['Laravel', 'FastAPI', 'FAISS'],
            'live_url' => 'https://example.com/lensku',
            'github_url' => null,
        ]);

        Skill::factory()->create(['name' => 'Laravel', 'category' => 'Backend']);
        Certificate::factory()->create(['title' => 'Backend Developer']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Lensku');
        $response->assertSee('Visual SKU retrieval application.');
        $response->assertSee('FAISS', false);
        $response->assertSee('Backend Developer');

        // Links only render when the underlying value exists.
        $response->assertSee('Live demo');
        $response->assertDontSee('Source');
    }

    public function test_homepage_never_renders_iframes(): void
    {
        Project::factory()->featured()->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('<iframe', false);
    }

    public function test_homepage_shows_empty_state_without_projects(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('No projects published yet');
    }

    public function test_projects_page_lists_published_projects_only(): void
    {
        Project::factory()->create(['title' => 'Undangly']);
        Project::factory()->archived()->create(['title' => 'Archived Thing']);

        $response = $this->get('/projects');

        $response->assertOk();
        $response->assertSee('Undangly');
        $response->assertDontSee('Archived Thing');
    }

    public function test_certificates_page_lists_certificates(): void
    {
        Certificate::factory()->create(['title' => 'Cloud Practitioner', 'issuer' => 'AWS']);

        $response = $this->get('/certificates');

        $response->assertOk();
        $response->assertSee('Cloud Practitioner');
        $response->assertSee('AWS');
    }

    public function test_contact_page_exposes_verified_channels_only(): void
    {
        config()->set('portfolio.contact.github.enabled', true);
        config()->set('portfolio.contact.linkedin.enabled', false);

        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSee('https://github.com/satriaranggaj');
        $response->assertSee('https://wa.me/628815695295', false);
        $response->assertDontSee('linkedin.com', false);
    }

    public function test_sitemap_lists_public_pages_and_case_studies(): void
    {
        Project::factory()->create(['slug' => 'lensku']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('/projects/lensku', false);
        $response->assertSee('<urlset', false);
    }

    public function test_disabled_contact_channel_does_not_render(): void
    {
        config()->set('portfolio.contact.facebook.enabled', false);

        $this->get('/')->assertDontSee('web.facebook.com', false);
    }
}
