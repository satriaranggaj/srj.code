<?php

namespace Tests\Feature\Admin;

use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Explicit administrator authorization.
 *
 * Registration is closed by default, but the `admin` middleware is the actual
 * control: it must hold even if registration is reopened.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $nonAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->nonAdmin = User::factory()->create();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminAreaProvider(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'project index' => ['/project'],
            'project create' => ['/project/create'],
            'skill index' => ['/skill'],
            'skill create' => ['/skill/create'],
            'certificate index' => ['/certificate'],
            'certificate create' => ['/certificate/create'],
            'messages' => ['/messages'],
        ];
    }

    /**
     * @dataProvider adminAreaProvider
     */
    public function test_guest_is_redirected_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    /**
     * @dataProvider adminAreaProvider
     */
    public function test_authenticated_non_admin_is_forbidden(string $uri): void
    {
        $this->actingAs($this->nonAdmin)->get($uri)->assertForbidden();
    }

    /**
     * @dataProvider adminAreaProvider
     */
    public function test_admin_is_allowed(string $uri): void
    {
        $this->actingAs($this->admin)->get($uri)->assertOk();
    }

    public function test_gate_matches_the_middleware(): void
    {
        // Gate resolves the *authenticated* user as the first argument, so the
        // subject must be selected with forUser() rather than passed as an argument.
        $this->assertTrue(Gate::forUser($this->admin)->allows('access-admin'));
        $this->assertFalse(Gate::forUser($this->nonAdmin)->allows('access-admin'));
        $this->assertFalse(Gate::forUser(null)->allows('access-admin'));
    }

    public function test_non_admin_cannot_create_a_project(): void
    {
        $this->actingAs($this->nonAdmin)
            ->post(route('project.store'), [
                'title' => 'Injected Project',
                'status' => Project::STATUS_LIVE,
            ])
            ->assertForbidden();

        $this->assertSame(0, Project::count());
    }

    public function test_non_admin_cannot_update_a_project(): void
    {
        $project = Project::factory()->create(['title' => 'Lensku']);

        $this->actingAs($this->nonAdmin)
            ->put(route('project.update', $project->id), [
                'title' => 'Hijacked',
                'status' => Project::STATUS_LIVE,
            ])
            ->assertForbidden();

        $this->assertSame('Lensku', $project->refresh()->title);
    }

    public function test_non_admin_cannot_delete_a_project(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->nonAdmin)
            ->delete(route('project.destroy', $project->id))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_non_admin_cannot_manage_technologies_certificates_or_messages(): void
    {
        $skill = Skill::factory()->create();
        $certificate = Certificate::factory()->create();
        $message = ContactMessage::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'A message long enough to satisfy validation.',
        ]);

        $this->actingAs($this->nonAdmin)->post(route('skill.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($this->nonAdmin)->delete(route('skill.destroy', $skill->id))->assertForbidden();
        $this->actingAs($this->nonAdmin)->post(route('certificate.store'), ['title' => 'X'])->assertForbidden();
        $this->actingAs($this->nonAdmin)->delete(route('certificate.destroy', $certificate->id))->assertForbidden();
        $this->actingAs($this->nonAdmin)->delete(route('message.destroy', $message->id))->assertForbidden();

        $this->assertDatabaseHas('skills', ['id' => $skill->id]);
        $this->assertDatabaseHas('certificates', ['id' => $certificate->id]);
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_can_still_perform_full_crud(): void
    {
        $this->actingAs($this->admin)
            ->post(route('project.store'), ['title' => 'Davina Event', 'status' => Project::STATUS_LIVE])
            ->assertRedirect(route('project.index'));

        $project = Project::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('project.update', $project->id), [
                'title' => 'Davina Event v2',
                'status' => Project::STATUS_LIVE,
            ])
            ->assertRedirect(route('project.index'));

        $this->assertSame('Davina Event v2', $project->refresh()->title);

        $this->actingAs($this->admin)
            ->delete(route('project.destroy', $project->id))
            ->assertRedirect(route('project.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_profile_routes_stay_available_to_non_admins(): void
    {
        // A non-admin may still manage their own account, just not the portfolio.
        $this->actingAs($this->nonAdmin)->get(route('profile.edit'))->assertOk();
    }

    public function test_a_registered_user_is_never_an_admin(): void
    {
        config(['portfolio.allow_registration' => true]);

        $this->post('/register', [
            'name' => 'Self Registered',
            'email' => 'selfregistered@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'selfregistered@example.com')->firstOrFail();

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->is_admin);

        // And the freshly registered account is actually locked out of the dashboard.
        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }
}
