<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin V2 presentation and shell regression.
 *
 * These assert the *structure* of the redesigned admin: one coherent shell, no
 * legacy design tokens leaking in, and the coupled delete/flash chains that the
 * redesign had to preserve. They are not a substitute for manual visual review.
 */
class AdminV2PresentationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Satria Rangga Jati']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminPageProvider(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'projects' => ['/project'],
            'project create' => ['/project/create'],
            'technologies' => ['/skill'],
            'technology create' => ['/skill/create'],
            'certificates' => ['/certificate'],
            'certificate create' => ['/certificate/create'],
            'messages' => ['/messages'],
            'profile' => ['/profile'],
        ];
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_every_admin_page_renders(string $uri): void
    {
        $this->actingAs($this->admin)->get($uri)->assertOk();
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_every_admin_page_shares_one_shell(string $uri): void
    {
        $html = $this->actingAs($this->admin)->get($uri)->assertOk()->getContent();

        // Shell markers: skip link, labelled nav, main landmark, drawer control.
        $this->assertStringContainsString('href="#admin-main"', $html);
        $this->assertStringContainsString('aria-label="Admin"', $html);
        $this->assertStringContainsString('id="admin-main"', $html);
        $this->assertStringContainsString('aria-controls="admin-navigation"', $html);
        $this->assertStringContainsString('Open navigation menu', $html);
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_admin_pages_are_never_indexed(string $uri): void
    {
        $this->actingAs($this->admin)
            ->get($uri)
            ->assertSee('content="noindex, nofollow"', false);
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_admin_pages_use_portfolio_v2_tokens_not_legacy_breeze_ones(string $uri): void
    {
        $html = $this->actingAs($this->admin)->get($uri)->assertOk()->getContent();

        // The old design language must be gone from rendered admin markup.
        foreach (['bg-primary-900', 'border-primary-700', 'text-primary-500', 'uppercase tracking-widest'] as $legacy) {
            $this->assertStringNotContainsString($legacy, $html, "Legacy class '{$legacy}' on {$uri}");
        }
    }

    /**
     * A shade that does not exist in tailwind.config.js is silently dropped by
     * Tailwind: the markup still renders, the class simply never emits CSS. That
     * failure is invisible in tests and in the HTML, so assert it directly.
     */
    public function test_every_design_token_used_in_views_actually_exists(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));
        $this->assertIsString($config);

        // Derive the real palette from tailwind.config.js rather than duplicating it.
        $palette = [];
        foreach (['ink', 'bone', 'accent'] as $family) {
            preg_match('/'.$family.':\s*\{(.*?)\}/s', $config, $m);
            $this->assertNotEmpty($m, "Missing '{$family}' palette in tailwind.config.js");

            preg_match_all('/(\d+):\s*[\'"#]/', $m[1], $shades);
            $palette[$family] = array_map('intval', $shades[1]);
        }

        $invalid = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            // NB: SplFileInfo::getExtension() returns 'php' for 'x.blade.php'.
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            preg_match_all('/\b(ink|bone|accent)-(\d{2,3})\b/', $contents, $tokens, PREG_SET_ORDER);

            foreach ($tokens as $token) {
                [$all, $family, $shade] = $token;

                if (! in_array((int) $shade, $palette[$family] ?? [], true)) {
                    $invalid[$all][] = $file->getFilename();
                }
            }
        }

        $this->assertSame(
            [],
            $invalid,
            "Undefined design token(s) in views (these emit no CSS):\n"
            .collect($invalid)->map(fn ($files, $token) => "  {$token} in ".implode(', ', array_unique($files)))
                ->implode("\n")
        );
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_admin_pages_load_no_third_party_font(string $uri): void
    {
        $html = $this->actingAs($this->admin)->get($uri)->assertOk()->getContent();

        foreach (['fonts.bunny.net', 'fonts.googleapis.com', 'fonts.gstatic.com'] as $host) {
            $this->assertStringNotContainsString($host, $html, "External font host {$host} on {$uri}");
        }
    }

    /**
     * @dataProvider adminPageProvider
     */
    public function test_admin_pages_do_not_load_the_legacy_navigation_stylesheet(string $uri): void
    {
        $this->actingAs($this->admin)
            ->get($uri)
            ->assertDontSee('frontend/style/navigation/navigation.css', false);
    }

    /**
     * The delete-confirmation chain is coupled: a [data-toggle="delete-button"]
     * element is repointed by admin.js and submitted through #form-delete.
     * Breaking either half silently disables every delete action.
     */
    public function test_the_delete_confirmation_chain_is_intact(): void
    {
        Project::factory()->create(['title' => 'Lensku']);
        Skill::factory()->create(['name' => 'Laravel']);
        Certificate::factory()->create(['title' => 'Backend']);
        ContactMessage::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'A message long enough to satisfy validation.',
        ]);

        // The hidden form the chain submits into.
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertSee('id="form-delete"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method"', false)
            ->assertSee('/script/admin.js', false);

        foreach (['/project', '/skill', '/certificate', '/messages'] as $uri) {
            $this->actingAs($this->admin)
                ->get($uri)
                ->assertSee('data-toggle="delete-button"', false);
        }
    }

    public function test_delete_actions_still_resolve_to_real_routes(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->admin)
            ->get('/project')
            ->assertSee(route('project.destroy', $project->id), false)
            ->assertSee(route('project.edit', $project->id), false);
    }

    public function test_flash_messages_render_inline_and_keep_the_session_shape(): void
    {
        // Controllers emit Session::get('message') as [[type, text]].
        $this->actingAs($this->admin)
            ->post(route('project.store'), ['title' => 'Lensku', 'status' => Project::STATUS_LIVE])
            ->assertRedirect(route('project.index'))
            ->assertSessionHas('message');

        $html = $this->actingAs($this->admin)->get('/project')->getContent();

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Data saved successfully.', $html);
    }

    public function test_validation_errors_are_surfaced_inline(): void
    {
        $this->actingAs($this->admin)
            ->from(route('project.create'))
            ->post(route('project.store'), ['title' => ''])
            ->assertSessionHasErrors('title');

        $html = $this->actingAs($this->admin)->get(route('project.create'))->getContent();

        $this->assertStringContainsString('role="alert"', $html);
    }

    public function test_empty_state_is_intentional_on_a_sparse_database(): void
    {
        foreach (['/project', '/skill', '/certificate', '/messages'] as $uri) {
            $html = $this->actingAs($this->admin)->get($uri)->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '/No (projects|technologies|certificates|messages) yet/',
                $html,
                "Missing empty state on {$uri}"
            );
        }
    }

    public function test_dashboard_shows_real_counts_only(): void
    {
        Project::factory()->count(3)->create();
        Project::factory()->featured()->create();
        Skill::factory()->count(2)->create();
        Certificate::factory()->create();

        $html = $this->actingAs($this->admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Projects', $html);

        // No fabricated analytics may be invented.
        foreach (['page views', 'visitors', 'revenue', 'growth', 'traffic'] as $fabricated) {
            $this->assertStringNotContainsStringIgnoringCase($fabricated, $html);
        }
    }

    public function test_guest_is_redirected_and_non_admin_is_forbidden(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get('/dashboard')->assertForbidden();
    }

    public function test_registration_stays_closed_by_default(): void
    {
        config(['portfolio.allow_registration' => false]);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 1); // only the admin created in setUp
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function authPageProvider(): array
    {
        return [
            'login' => ['/login'],
            'forgot password' => ['/forgot-password'],
        ];
    }

    /**
     * @dataProvider authPageProvider
     */
    public function test_auth_pages_share_one_shell(string $uri): void
    {
        $html = $this->get($uri)->assertOk()->getContent();

        // Auth V2 markers: branding panel, back link, noindex, welcome heading.
        $this->assertStringContainsString('Portfolio Administration', $html);
        $this->assertStringContainsString('Back to portfolio', $html);
        $this->assertStringContainsString('content="noindex, nofollow"', $html, false);

        // The old Breeze light island must be gone.
        $this->assertStringNotContainsString('bg-gray-100', $html);
        $this->assertStringNotContainsString('<svg viewBox="0 0 316 316"', $html);
    }

    public function test_login_has_the_expected_branding_and_controls(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('Welcome back', $html);
        $this->assertStringContainsString('Sign in to manage the', $html);
        $this->assertStringContainsString('autocomplete="username"', $html);
        $this->assertStringContainsString('autocomplete="current-password"', $html);
        $this->assertStringContainsString('Remember me', $html);
        $this->assertStringContainsString('Forgot password?', $html);

        // Password visibility toggle is a real button, not a div.
        $this->assertStringContainsString('Show password', $html);
        $this->assertStringNotContainsString('type="subject"', $html);
    }

    public function test_reset_password_view_renders(): void
    {
        $html = $this->get('/reset-password/some-token')->assertOk()->getContent();

        $this->assertStringContainsString('Choose a new password', $html);
        $this->assertStringContainsString('name="token" value="some-token"', $html);
        $this->assertStringContainsString('autocomplete="new-password"', $html);
    }

    public function test_public_portfolio_pages_are_untouched_by_the_admin_redesign(): void
    {
        foreach (['/', '/about', '/projects', '/certificates', '/contact'] as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            // Admin chrome must never appear on a public page.
            $this->assertStringNotContainsString('id="admin-main"', $html);
            $this->assertStringNotContainsString('Skip to content" class="sr-only focus:not-sr-only', $html);

            // Public shell markers must still be present.
            $this->assertStringContainsString('id="main"', $html);
            $this->assertStringContainsString('Skip to content', $html);
        }
    }

    public function test_sitemap_and_robots_are_unchanged(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:');
    }

    public function test_admin_layout_loads_no_public_only_asset(): void
    {
        $html = $this->actingAs($this->admin)->get('/dashboard')->getContent();

        // jQuery/Toastr/SweetAlert2 are admin-only delete + flash dependencies.
        $this->assertStringContainsString('jquery-3.7.0.min.js', $html);
        $this->assertStringContainsString('toastr', $html);

        // ...and the public portfolio never loads them.
        $this->get('/')->assertDontSee('jquery', false);
    }
}
