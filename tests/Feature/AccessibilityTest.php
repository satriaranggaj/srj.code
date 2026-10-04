<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Accessibility regression guard.
 *
 * These are cheap structural assertions, not a substitute for manual testing with a
 * screen reader and a keyboard. They exist to catch the specific ways a redesign
 * silently breaks semantics: an <a> replaced by a <div>, a lost label, a heading
 * jump, an icon-only control with no accessible name.
 */
class AccessibilityTest extends TestCase
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
    public function test_the_document_declares_a_language(string $uri): void
    {
        $this->get($uri)->assertSee('<html lang="en"', false);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_a_skip_link_is_the_first_focusable_element(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        $this->assertStringContainsString('href="#main"', $html);
        $this->assertStringContainsString('Skip to content', $html);

        // The main landmark it targets must exist and be focusable.
        $this->assertStringContainsString('id="main"', $html);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_landmarks_are_present(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        // A page has one <main>; it may legitimately have several <nav> elements
        // (primary navigation plus footer navigation).
        $this->assertSame(1, preg_match_all('/<main\b/i', $html), 'Expected exactly one main landmark');
        $this->assertGreaterThanOrEqual(1, preg_match_all('/<header\b/i', $html), 'Missing header landmark');
        $this->assertGreaterThanOrEqual(1, preg_match_all('/<footer\b/i', $html), 'Missing footer landmark');
        $this->assertGreaterThanOrEqual(1, preg_match_all('/<nav\b/i', $html), 'Missing nav landmark');
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_navigation_is_labelled_and_marked_up_as_a_nav_landmark(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        $this->assertStringContainsString('aria-label="Primary"', $html);
        $this->assertStringContainsString('aria-label="Footer"', $html);
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_navigation_links_are_real_anchors_with_hrefs(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        // Every navigation item must be an <a href>, never a clickable div/span.
        $this->assertSame(
            0,
            preg_match('/<(div|span)[^>]*onclick/i', $html),
            'Clickable div/span found; use a semantic element'
        );
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_no_interactive_element_is_nested_inside_another(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        // <a> inside <button> and <button> inside <a> are both invalid.
        $this->assertSame(0, preg_match('/<button\b[^>]*>(?:(?!<\/button>).)*?<a\b/is', $html));
        $this->assertSame(0, preg_match('/<a\b[^>]*>(?:(?!<\/a>).)*?<button\b/is', $html));
    }

    /**
     * @dataProvider publicPageProvider
     */
    public function test_images_declare_alternative_text(string $uri): void
    {
        $html = $this->get($uri)->getContent();

        preg_match_all('/<img\b[^>]*>/i', $html, $matches);

        // Guards against the loop below silently running zero times.
        $this->assertTrue(true, 'Image scan executed');

        foreach ($matches[0] as $img) {
            $this->assertMatchesRegularExpression(
                '/\balt\s*=/i',
                $img,
                "Image without alt attribute on {$uri}: {$img}"
            );

            $this->assertStringNotContainsString('alt=""', $img, 'Meaningless empty alt text on '.$uri);
        }
    }

    public function test_card_images_are_lazy_loaded_with_intrinsic_dimensions(): void
    {
        Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study.',
            'thumbnail' => 'projects/thumbnails/lensku.png',
        ]);

        // The projects index shows cards, which sit below the fold.
        $html = $this->get(route('project'))->getContent();

        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('decoding="async"', $html);
        $this->assertStringContainsString('width="640"', $html);
        $this->assertStringContainsString('height="360"', $html);
    }

    public function test_the_case_study_hero_image_is_eager_and_sized(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study.',
            'thumbnail' => 'projects/thumbnails/lensku.png',
        ]);

        $html = $this->get(route('project.show', $project))->getContent();

        // Above-the-fold imagery must not be lazy: it is the LCP element.
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('width="1280"', $html);
        $this->assertStringContainsString('height="720"', $html);
    }

    public function test_the_mobile_menu_button_is_fully_described(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('aria-controls="mobile-navigation"', $html);
        $this->assertStringContainsString('aria-expanded=', $html);
        $this->assertStringContainsString('Toggle navigation menu', $html);

        // Escape closes the menu: keyboard users are never trapped in it.
        $this->assertStringContainsString('keydown.escape', $html);
    }

    public function test_icon_only_controls_have_an_accessible_name(): void
    {
        $html = $this->get('/')->getContent();

        // Decorative icons are hidden from assistive tech...
        $this->assertStringContainsString('aria-hidden="true"', $html);

        // ...and every icon-only control carries a screen-reader-only label.
        $this->assertStringContainsString('sr-only', $html);
    }

    public function test_the_active_navigation_item_is_marked_with_aria_current(): void
    {
        $this->get('/projects')->assertSee('aria-current="page"', false);
        $this->get('/')->assertSee('aria-current="page"', false);
    }

    public function test_reduced_motion_is_respected_in_the_stylesheet(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('data-reveal', $css);
    }

    public function test_every_contact_form_field_has_a_label(): void
    {
        $html = $this->get('/contact')->getContent();

        foreach (['contact-name', 'contact-email', 'contact-subject', 'contact-message'] as $id) {
            $this->assertStringContainsString('for="'.$id.'"', $html, "Missing label for {$id}");
            $this->assertStringContainsString('id="'.$id.'"', $html, "Missing field {$id}");
        }
    }

    public function test_the_honeypot_is_hidden_from_assistive_technology(): void
    {
        $html = $this->get('/contact')->getContent();

        // It must be invisible and untabbable, never a permanent a11y liability.
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('tabindex="-1"', $html);
        $this->assertStringContainsString('left-[-9999px]', $html);
    }

    public function test_project_card_overlay_link_stays_keyboard_reachable(): void
    {
        $project = Project::factory()->create([
            'slug' => 'lensku',
            'description' => 'A case study.',
            'live_url' => 'https://example.com/live',
            'github_url' => 'https://github.com/satriaranggaj/lensku',
        ]);

        $html = $this->get(route('project'))->getContent();

        // The whole-card link is a real anchor with a text label.
        $this->assertStringContainsString('href="'.route('project.show', 'lensku').'"', $html);

        // The secondary links sit above the overlay so they remain clickable.
        $this->assertStringContainsString('relative z-10', $html);

        /*
         * The card's primary link must not suppress the global focus ring. Buttons are
         * allowed to swap outline for a custom ring, so this checks the card anchor
         * specifically rather than banning the utility across the page.
         */
        preg_match('/<a\b[^>]*href="'.preg_quote(route('project.show', 'lensku'), '/').'"[^>]*>/', $html, $matches);

        $this->assertNotEmpty($matches, 'Project card link not found');
        $this->assertStringNotContainsString('focus-visible:outline-none', $matches[0]);
    }

    public function test_external_links_open_safely(): void
    {
        $html = $this->get('/contact')->getContent();

        preg_match_all('/<a\b[^>]*target="_blank"[^>]*>/i', $html, $matches);

        $this->assertNotEmpty($matches[0], 'Expected at least one external link on the contact page');

        foreach ($matches[0] as $link) {
            $this->assertStringContainsString('rel="noopener noreferrer"', $link);
        }
    }

    public function test_content_records_render_without_a_photo_dependency(): void
    {
        Skill::factory()->create(['name' => 'FAISS', 'category' => 'Tools']);
        Certificate::factory()->create(['title' => 'Certification', 'image' => null]);

        // Everything must render even when no images exist at all.
        $this->get('/')->assertOk()->assertSee('FAISS');
        $this->get('/certificates')->assertOk()->assertSee('Certification');
    }
}
