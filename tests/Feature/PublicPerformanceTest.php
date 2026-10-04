<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Performance regression guard for the public site.
 *
 * The expensive mistakes this prevents are all ones that were present before
 * Portfolio V2: cross-origin iframes, a 6 MB photo, two Google Fonts families at ten
 * weights each, jQuery on pages that never used it, and duplicate vendor bundles.
 */
class PublicPerformanceTest extends TestCase
{
    use RefreshDatabase;

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
    public function test_no_page_embeds_a_project_in_an_iframe(string $uri): void
    {
        $this->get($uri)->assertDontSee('<iframe', false);
    }

    public function test_a_project_with_a_live_url_still_renders_without_an_iframe(): void
    {
        Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study.',
            'live_url' => 'https://example.com/app',
        ]);

        $this->get(route('project.show', 'lensku'))->assertDontSee('<iframe', false);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_no_bootstrap_jquery_or_icon_font_on_public_pages(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        foreach ([
            'bootstrap',
            'jquery',
            'fontawesome',
            'font-awesome',
            'ckeditor',
            'lightbox',
            'swiper',
            'toastr',
            'sweetalert',
        ] as $legacy) {
            $this->assertStringNotContainsStringIgnoringCase(
                $legacy,
                $html,
                "Legacy dependency '{$legacy}' leaked onto {$uri}"
            );
        }
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_no_third_party_font_requests(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        foreach (['fonts.googleapis.com', 'fonts.gstatic.com', 'fonts.bunny.net', 'use.typekit.net'] as $host) {
            $this->assertStringNotContainsString($host, $html, "External font host {$host} on {$uri}");
        }
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_exactly_one_stylesheet_and_one_script_bundle(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        // Vite emits one <link rel="stylesheet"> and one module script per page.
        preg_match_all('/<link\b[^>]*rel="stylesheet"[^>]*>/i', $html, $styles);
        preg_match_all('/<script\b[^>]*\bsrc=/i', $html, $scripts);

        $this->assertCount(1, $styles[0], "Expected exactly one stylesheet on {$uri}");
        $this->assertCount(1, $scripts[0], "Expected exactly one external script on {$uri}");
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_no_preconnect_to_the_sites_own_origin(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        preg_match_all('/<link\b[^>]*rel="preconnect"[^>]*href="([^"]+)"[^>]*>/i', $html, $matches);

        $ownOrigin = [rtrim(config('app.url'), '/').'/', '/'];

        foreach ($matches[1] as $href) {
            $this->assertNotContains(
                rtrim($href, '/').'/',
                $ownOrigin,
                'Preconnecting to the site\'s own origin provides no benefit'
            );
        }

        // Asserted unconditionally so the check cannot pass by inspecting nothing.
        $this->assertIsArray($matches[1]);
    }

    public function test_the_public_site_does_not_load_the_admin_bundle(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('admin.js', $html);
        $this->assertStringNotContainsString('/libraries/', $html);
    }

    public function test_no_animation_library_is_present(): void
    {
        $bundles = glob(public_path('build/assets/app-*.js')) ?: [];

        $this->assertNotEmpty($bundles, 'No JS bundle found; run `npm run build`');

        $js = file_get_contents($bundles[0]);

        foreach (['gsap', 'anime', 'framer-motion', 'locomotive', 'ScrollMagic'] as $library) {
            $this->assertStringNotContainsString($library, $js, "Animation library {$library} in the bundle");
        }
    }

    public function test_the_build_output_is_used_rather_than_the_vite_dev_server(): void
    {
        // A leftover public/hot file makes @vite emit dev-server URLs instead of the
        // hashed production bundle, which silently invalidates every perf assertion.
        $this->assertFileDoesNotExist(public_path('hot'));

        $this->get('/')->assertDontSee('@vite/client', false);
    }

    public function test_the_production_bundle_is_a_reasonable_size(): void
    {
        $assets = glob(public_path('build/assets/app-*.js')) ?: [];
        $css = glob(public_path('build/assets/app-*.css')) ?: [];

        $this->assertNotEmpty($assets, 'No JS bundle found; run `npm run build`');
        $this->assertNotEmpty($css, 'No CSS bundle found; run `npm run build`');

        // Generous ceilings: these fail only on a real regression, not on small drift.
        $this->assertLessThan(300 * 1024, filesize($assets[0]), 'JS bundle unexpectedly large');
        $this->assertLessThan(120 * 1024, filesize($css[0]), 'CSS bundle unexpectedly large');
    }

    public function test_legacy_vendor_assets_are_not_back_in_the_repository(): void
    {
        foreach ([
            'libraries/ckeditor',
            'libraries/lightbox2',
            'libraries/swiper',
            'frontend/libraries/bootstrap',
            'frontend/libraries/fontawesome',
            'frontend/jquery',
            'script/bootstrap.min.js',
            'script/bootstrap.bundle.min.js',
        ] as $path) {
            $this->assertFileDoesNotExist(
                public_path($path),
                "Legacy asset reintroduced: {$path}"
            );
        }
    }

    public function test_the_public_asset_tree_stays_small(): void
    {
        $total = 0;
        $files = 0;

        foreach (glob(public_path('**/*'), GLOB_NOSORT) as $entry) {
            if (! is_file($entry) || str_contains($entry, 'build')) {
                continue;
            }

            $total += filesize($entry);
            $files++;
        }

        // Legacy versions carried ~37 MB across ~2 300 files.
        $this->assertLessThan(2 * 1024 * 1024, $total, 'Public asset tree has grown unexpectedly large');
        $this->assertLessThan(20, $files, 'Public asset tree has accumulated unexpected files');
    }

    public function test_listing_pages_do_not_query_more_than_a_reasonable_number_of_times(): void
    {
        \Illuminate\Support\Facades\DB::enableQueryLog();

        $this->get(route('project'))->assertOk();

        $count = count(\Illuminate\Support\Facades\DB::getQueryLog());

        \Illuminate\Support\Facades\DB::disableQueryLog();

        // One count + one select for the listing, plus session bookkeeping.
        $this->assertLessThanOrEqual(8, $count, "Projects page ran {$count} queries");
    }
}
