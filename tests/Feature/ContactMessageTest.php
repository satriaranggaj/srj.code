<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('contact');
    }

    public function test_contact_page_renders_the_form(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('name="message"', false);
        $response->assertSee('name="website"', false);
        $response->assertSee(route('contact.store'), false);
    }

    public function test_valid_message_is_stored(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Recruiter Name',
            'email' => 'recruiter@example.com',
            'subject' => 'Full stack role',
            'message' => 'We would like to discuss a position with you.',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('contact_status', 'success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Recruiter Name',
            'email' => 'recruiter@example.com',
            'subject' => 'Full stack role',
        ]);
    }

    public function test_message_is_stored_without_a_subject(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'message' => 'Just a short note about a project.',
        ])->assertSessionHas('contact_status', 'success');

        $this->assertDatabaseHas('contact_messages', ['name' => 'Someone']);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'x',
            'email' => 'not-an-email',
            'message' => 'short',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_honeypot_rejects_bot_submissions(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'message' => 'Buy cheap backlinks for your website now.',
            'website' => 'https://spam.example.com',
        ]);

        $response->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_submissions_are_rate_limited(): void
    {
        $limit = (int) config('portfolio.contact_form.max_per_minute');

        for ($i = 0; $i < $limit; $i++) {
            $this->post(route('contact.store'), [
                'name' => 'Visitor '.$i,
                'email' => "visitor{$i}@example.com",
                'message' => 'A perfectly legitimate message number '.$i.'.',
            ])->assertSessionHas('contact_status', 'success');
        }

        $this->post(route('contact.store'), [
            'name' => 'One Too Many',
            'email' => 'overflow@example.com',
            'message' => 'This submission exceeds the configured limit.',
        ])->assertStatus(429);

        $this->assertDatabaseCount('contact_messages', $limit);
    }

    public function test_ip_and_user_agent_are_recorded(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'A message that is long enough to pass validation.',
        ]);

        $message = ContactMessage::firstOrFail();

        $this->assertNotNull($message->ip_address);
        $this->assertFalse($message->is_read);
    }
}
