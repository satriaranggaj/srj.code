<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Registration is closed by default so that a visitor cannot create an
     * account and reach the dashboard, which can edit and delete every project,
     * technology and certificate.
     */
    public function test_registration_screen_is_not_publicly_available_by_default(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_submission_is_rejected_by_default(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_registration_screen_can_be_rendered_when_explicitly_enabled(): void
    {
        config(['portfolio.allow_registration' => true]);

        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register_when_explicitly_enabled(): void
    {
        config(['portfolio.allow_registration' => true]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
