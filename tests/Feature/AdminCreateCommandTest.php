<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_administrator(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Satria Rangga Jati',
            '--email' => 'owner@example.com',
            '--password' => 'a-perfectly-fine-password',
        ])->assertSuccessful();

        $user = User::where('email', 'owner@example.com')->firstOrFail();

        $this->assertSame('Satria Rangga Jati', $user->name);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('a-perfectly-fine-password', $user->password));
    }

    public function test_it_never_stores_the_password_in_plain_text(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Owner',
            '--email' => 'owner@example.com',
            '--password' => 'a-perfectly-fine-password',
        ])->assertSuccessful();

        $hash = User::where('email', 'owner@example.com')->value('password');

        $this->assertNotSame('a-perfectly-fine-password', $hash);
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function test_the_new_administrator_can_reach_the_dashboard(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Owner',
            '--email' => 'owner@example.com',
            '--password' => 'a-perfectly-fine-password',
        ])->assertSuccessful();

        $user = User::where('email', 'owner@example.com')->firstOrFail();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_it_fails_safely_when_the_email_already_exists(): void
    {
        User::factory()->admin()->create(['email' => 'owner@example.com']);

        $this->artisan('admin:create', [
            '--name' => 'Impostor',
            '--email' => 'owner@example.com',
            '--password' => 'a-perfectly-fine-password',
        ])->assertFailed();

        // The original account is untouched.
        $this->assertSame(1, User::where('email', 'owner@example.com')->count());
        $this->assertNotSame('Impostor', User::where('email', 'owner@example.com')->value('name'));
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Owner',
            '--email' => 'not-an-email',
            '--password' => 'a-perfectly-fine-password',
        ])->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_it_rejects_a_password_that_fails_the_default_rules(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Owner',
            '--email' => 'owner@example.com',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_it_requires_a_value_for_each_prompted_field(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Name', '')
            ->expectsQuestion('E-mail address', '')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }
}
