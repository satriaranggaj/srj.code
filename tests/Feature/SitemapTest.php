<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SimpleXMLElement;
use Tests\TestCase;

/**
 * The sitemap must never advertise a URL that returns 404.
 */
class SitemapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The sitemap is built from APP_URL, not from the request, so expectations must
     * be derived the same way rather than from route().
     */
    private function expected(string $path): string
    {
        return rtrim((string) config('app.url'), '/').$path;
    }

    /**
     * @return array<int, string>
     */
    private function locations(): array
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        // Proves the document is well-formed XML, not just a string.
        $element = new SimpleXMLElement($xml);

        $locations = [];

        foreach ($element->url as $url) {
            $locations[] = (string) $url->loc;
        }

        return $locations;
    }

    public function test_the_response_is_valid_xml_with_an_xml_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset', false);
    }

    public function test_all_static_public_pages_are_listed(): void
    {
        $locations = $this->locations();

        foreach (['/', '/projects', '/about', '/certificates', '/contact'] as $path) {
            $this->assertContains($this->expected($path), $locations, "Missing static page: {$path}");
        }
    }

    public function test_static_pages_do_not_claim_a_lastmod(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        // A per-request timestamp would falsely report every page as modified on
        // every crawl. Static page URLs must carry no <lastmod> at all.
        $homeEntry = $this->entryFor($xml, $this->expected('/'));

        $this->assertStringNotContainsString('<lastmod>', $homeEntry);
    }

    public function test_a_project_with_a_case_study_is_listed(): void
    {
        Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study with real content.',
        ]);

        $this->assertContains($this->expected('/projects/lensku'), $this->locations());
    }

    public function test_a_project_entry_carries_its_real_lastmod(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study with real content.',
            'updated_at' => '2026-01-15 10:00:00',
        ]);

        $entry = $this->entryFor($this->get('/sitemap.xml')->getContent(), $this->expected('/projects/lensku'));

        $this->assertStringContainsString('<lastmod>', $entry);
        $this->assertStringContainsString('2026-01-15', $entry);
    }

    public function test_every_location_is_built_from_app_url_not_the_request_host(): void
    {
        config(['app.url' => 'https://satriarangga.my.id']);

        $project = Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study with real content.',
        ]);

        // A preview/staging hostname must not leak into the sitemap, because it would
        // contradict the APP_URL-derived canonical tags and the robots.txt Sitemap line.
        $body = $this->get('/sitemap.xml', ['HTTP_HOST' => 'staging.example.com'])
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('staging.example.com', $body);

        foreach ($this->locations() as $location) {
            $this->assertStringStartsWith('https://satriarangga.my.id/', $location);
        }

        $this->assertContains('https://satriarangga.my.id/projects/lensku', $this->locations());
    }

    public function test_an_archived_project_is_not_listed(): void
    {
        Project::factory()->archived()->create([
            'slug' => 'withdrawn',
            'description' => 'Withdrawn but has content.',
        ]);

        $this->assertNotContains($this->expected('/projects/withdrawn'), $this->locations());
    }

    public function test_a_project_without_a_case_study_is_not_listed(): void
    {
        Project::factory()->create([
            'slug' => 'bare-card',
            'description' => null,
            'problem' => null,
            'solution' => null,
            'highlights' => null,
            'challenges' => null,
            'outcome' => null,
            'screenshots' => null,
        ]);

        $this->assertNotContains($this->expected('/projects/bare-card'), $this->locations());
    }

    public function test_every_listed_url_actually_resolves(): void
    {
        Project::factory()->create(['slug' => 'live-one', 'description' => 'Has content.']);
        Project::factory()->create([
            'slug' => 'in-progress-one',
            'status' => Project::STATUS_IN_PROGRESS,
            'description' => 'Also has content.',
        ]);
        Project::factory()->archived()->create(['slug' => 'archived-one', 'description' => 'Withdrawn.']);
        Project::factory()->create(['slug' => 'empty-one', 'description' => null, 'outcome' => null]);

        foreach ($this->locations() as $location) {
            // Strip the host so the request stays inside the test application.
            $path = parse_url($location, PHP_URL_PATH) ?: '/';

            $this->get($path)->assertOk("Sitemap advertises a URL that does not resolve: {$location}");
        }
    }

    private function entryFor(string $xml, string $location): string
    {
        $element = new SimpleXMLElement($xml);

        foreach ($element->url as $url) {
            if ((string) $url->loc === $location) {
                return $url->asXML();
            }
        }

        $this->fail("URL not present in sitemap: {$location}");
    }
}
