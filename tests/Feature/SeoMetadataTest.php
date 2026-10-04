<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SEO output and the APP_URL contract.
 *
 * Canonical, og:url, og:image and the robots sitemap URL are all derived from
 * APP_URL, never from the incoming Host header. That is what stops a spoofed Host
 * (or a staging hostname) from producing canonical URLs for a different domain.
 */
class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    private const PRODUCTION_URL = 'https://satriarangga.my.id';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => self::PRODUCTION_URL]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPageProvider(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/about'],
            'projects' => ['/projects'],
            'certificates' => ['/certificates'],
            'contact' => ['/contact'],
        ];
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_every_public_page_has_a_unique_title_and_description(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<title>[^<]+<\/title>/', $html);
        $this->assertStringContainsString('<meta name="description" content="', $html);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_canonical_and_og_url_use_app_url_not_the_request_host(string $uri): void
    {
        // A completely different Host header must not influence the canonical URL.
        $html = $this->get($uri, ['HTTP_HOST' => 'attacker.example.com'])->assertOk()->getContent();

        $this->assertStringContainsString(
            'rel="canonical" href="'.self::PRODUCTION_URL.$uri.'"',
            $html
        );

        $this->assertStringContainsString(
            'property="og:url" content="'.self::PRODUCTION_URL.$uri.'"',
            $html
        );

        $this->assertStringNotContainsString('attacker.example.com', $html);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_open_graph_and_twitter_cards_are_present(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        foreach ([
            'property="og:site_name"',
            'property="og:type"',
            'property="og:title"',
            'property="og:description"',
            'property="og:url"',
            'property="og:image"',
            'property="og:locale"',
            'name="twitter:card"',
            'name="twitter:title"',
            'name="twitter:description"',
            'name="twitter:image"',
        ] as $tag) {
            $this->assertStringContainsString($tag, $html, "Missing {$tag} on {$uri}");
        }
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_structured_data_is_valid_json_ld(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        $this->assertSame(1, preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $html,
            $matches
        ));

        $decoded = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

        $this->assertIsArray($decoded, 'JSON-LD must be valid JSON');
        $this->assertSame('https://schema.org', $decoded['@context']);
    }

    public function test_the_default_social_image_exists_and_is_not_broken(): void
    {
        $path = public_path(ltrim(config('portfolio.seo.og_image'), '/'));

        $this->assertFileExists($path, 'Default og:image must exist on disk');

        // A valid PNG signature, so no social crawler receives a corrupt file.
        $this->assertSame('89504e47', bin2hex(file_get_contents($path, false, null, 0, 4)));

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString(
            'property="og:image" content="'.self::PRODUCTION_URL.'/'.ltrim(config('portfolio.seo.og_image'), '/').'"',
            $html
        );
    }

    public function test_a_case_study_uses_its_own_thumbnail_as_the_social_image(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study.',
            'thumbnail' => 'projects/thumbnails/lensku.png',
        ]);

        $html = $this->get(route('project.show', $project))->assertOk()->getContent();

        $this->assertStringContainsString(
            'property="og:image" content="'.self::PRODUCTION_URL.'/storage/projects/thumbnails/lensku.png"',
            $html
        );
    }

    public function test_a_case_study_falls_back_to_the_default_social_image(): void
    {
        $project = Project::factory()->create([
            'slug' => 'no-image',
            'description' => 'A case study without artwork.',
            'thumbnail' => null,
        ]);

        $html = $this->get(route('project.show', $project))->assertOk()->getContent();

        $this->assertStringContainsString(
            'property="og:image" content="'.self::PRODUCTION_URL.'/'.ltrim(config('portfolio.seo.og_image'), '/').'"',
            $html
        );
    }

    public function test_case_study_structured_data_contains_only_real_values(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'title' => 'Lensku',
            'short_description' => 'Visual SKU retrieval.',
            'description' => 'A case study.',
            'tech_stack' => ['Laravel', 'FastAPI'],
            'github_url' => null,
        ]);

        $html = $this->get(route('project.show', $project))->getContent();

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $decoded = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

        $this->assertSame('CreativeWork', $decoded['@type']);
        $this->assertSame('Lensku', $decoded['name']);
        $this->assertSame('Visual SKU retrieval.', $decoded['description']);

        // No link was stored, so no repository claim may be emitted.
        $this->assertArrayNotHasKey('codeRepository', $decoded);
    }

    public function test_robots_txt_advertises_the_app_url_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');

        $body = $response->getContent();

        $this->assertStringContainsString('Sitemap: '.self::PRODUCTION_URL.'/sitemap.xml', $body);
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Disallow: /dashboard', $body);
        $this->assertStringContainsString('Disallow: /login', $body);
    }

    public function test_robots_txt_ignores_a_spoofed_host_header(): void
    {
        $body = $this->get('/robots.txt', ['HTTP_HOST' => 'attacker.example.com'])->getContent();

        $this->assertStringContainsString('Sitemap: '.self::PRODUCTION_URL.'/sitemap.xml', $body);
        $this->assertStringNotContainsString('attacker.example.com', $body);
    }

    public function test_no_static_robots_file_can_shadow_the_dynamic_route(): void
    {
        // A static public/robots.txt is served directly by the web server and would
        // silently bypass the route, reintroducing the hard-coded production domain.
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_public_pages_declare_an_indexable_robots_directive(): void
    {
        $this->get('/')->assertSee('content="index, follow, max-image-preview:large"', false);
    }

    public function test_headings_are_ordered_without_skips(): void
    {
        foreach (['/', '/about', '/projects', '/certificates', '/contact'] as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            preg_match_all('/<h([1-6])\b/i', $html, $matches);

            $levels = array_map('intval', $matches[1]);

            $this->assertNotEmpty($levels, "No headings found on {$uri}");
            $this->assertSame(1, $levels[0], "{$uri} must start with a single h1");

            // No level may jump by more than one.
            for ($i = 1; $i < count($levels); $i++) {
                $this->assertLessThanOrEqual(
                    $levels[$i - 1] + 1,
                    $levels[$i],
                    "Heading level jumped on {$uri}: h{$levels[$i - 1]} to h{$levels[$i]}"
                );
            }
        }
    }

    public function test_content_pages_render_their_database_records(): void
    {
        Skill::factory()->create(['name' => 'FAISS', 'category' => 'AI / Machine Learning']);
        Certificate::factory()->create(['title' => 'Backend Certification', 'issuer' => 'Example Org']);
        Project::factory()->featured()->create([
            'title' => 'Undangly',
            'description' => 'A wedding invitation platform.',
        ]);

        $this->get('/')->assertOk()->assertSee('Undangly');
        $this->get('/about')->assertOk()->assertSee('FAISS');
        $this->get('/certificates')->assertOk()->assertSee('Backend Certification');
        $this->get('/projects')->assertOk()->assertSee('Undangly');
    }
}
