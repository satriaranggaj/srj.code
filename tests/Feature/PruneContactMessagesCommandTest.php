<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneContactMessagesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function message(string $createdAt): ContactMessage
    {
        $message = ContactMessage::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'A message long enough to satisfy validation.',
        ]);

        // created_at is guarded against mass assignment, so age it explicitly.
        $message->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $message;
    }

    public function test_it_deletes_only_messages_older_than_the_retention_window(): void
    {
        config(['portfolio.contact_retention_days' => 90]);

        $old = $this->message(now()->subDays(120)->toDateTimeString());
        $recent = $this->message(now()->subDays(10)->toDateTimeString());

        $this->artisan('contact:prune')->assertSuccessful();

        $this->assertDatabaseMissing('contact_messages', ['id' => $old->id]);
        $this->assertDatabaseHas('contact_messages', ['id' => $recent->id]);
    }

    public function test_the_retention_window_is_configurable(): void
    {
        config(['portfolio.contact_retention_days' => 7]);

        $old = $this->message(now()->subDays(30)->toDateTimeString());

        $this->artisan('contact:prune')->assertSuccessful();

        $this->assertDatabaseMissing('contact_messages', ['id' => $old->id]);
    }

    public function test_nothing_is_deleted_without_an_explicit_command(): void
    {
        $this->message(now()->subYears(5)->toDateTimeString());

        // Retention is never automatic.
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_a_dry_run_reports_without_deleting(): void
    {
        $old = $this->message(now()->subDays(200)->toDateTimeString());

        $this->artisan('contact:prune --dry-run')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();

        $this->assertDatabaseHas('contact_messages', ['id' => $old->id]);
    }

    public function test_the_days_option_overrides_the_configuration(): void
    {
        config(['portfolio.contact_retention_days' => 365]);

        $message = $this->message(now()->subDays(40)->toDateTimeString());

        $this->artisan('contact:prune --days=30')->assertSuccessful();

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_it_rejects_a_nonsense_window(): void
    {
        $this->message(now()->toDateTimeString());

        $this->artisan('contact:prune --days=0')->assertFailed();

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_it_is_a_no_op_when_nothing_is_expired(): void
    {
        $this->message(now()->toDateTimeString());

        $this->artisan('contact:prune')->assertSuccessful();

        $this->assertDatabaseCount('contact_messages', 1);
    }
}
