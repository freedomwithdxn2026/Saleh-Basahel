<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadCommunication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCrmFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_webhook_creates_and_updates_one_scored_lead(): void
    {
        $this->postJson('/api/leads', [
            'external_id' => 'whatsapp:+971500000001',
            'source' => 'whatsapp',
            'name' => 'Test Lead',
            'phone' => '+971500000001',
            'country' => 'UAE',
            'occupation' => 'Designer',
            'interest' => 'both',
            'consent' => true,
            'conversation_history' => [['role' => 'user', 'text' => 'I would like to learn more.']],
        ])->assertCreated();

        $this->postJson('/api/leads', [
            'external_id' => 'whatsapp:+971500000001',
            'source' => 'whatsapp',
            'email' => 'lead@example.com',
            'goals' => 'Build useful skills',
            'available_time_per_week' => '5 hours',
            'conversation_history' => [['role' => 'user', 'text' => 'I can give five hours each week.']],
        ])->assertCreated();

        $this->assertDatabaseCount('leads', 1);
        $lead = Lead::firstOrFail();

        $this->assertSame('Test Lead', $lead->name);
        $this->assertSame('lead@example.com', $lead->email);
        $this->assertGreaterThanOrEqual(40, $lead->lead_score);
        $this->assertStringContainsString('five hours', $lead->conversation_history);
        $this->assertNotNull($lead->automation_enrolled_at);
        $this->assertGreaterThanOrEqual(2, LeadCommunication::where('lead_id', $lead->id)->where('direction', 'inbound')->count());
        $this->assertDatabaseHas('lead_communications', [
            'lead_id' => $lead->id,
            'channel' => 'whatsapp',
            'body' => 'I would like to learn more.',
            'status' => 'received',
        ]);
    }

    public function test_landing_page_submission_immediately_enrolls_welcome_delivery(): void
    {
        $this->post('/en/leads', [
            'name' => 'Landing Lead',
            'phone' => '+971 50 000 0003',
            'email' => 'landing@example.com',
            'country' => 'UAE',
            'interest' => 'both',
            'consent' => '1',
        ])->assertRedirect();

        $lead = Lead::where('email', 'landing@example.com')->firstOrFail();

        $this->assertSame('landing_page', $lead->source);
        $this->assertTrue($lead->consent);
        $this->assertNotNull($lead->automation_enrolled_at);
        $this->assertDatabaseHas('lead_communications', [
            'lead_id' => $lead->id,
            'channel' => 'website',
            'category' => 'lead_submission',
            'status' => 'received',
        ]);
        $this->assertDatabaseHas('lead_communications', [
            'lead_id' => $lead->id,
            'channel' => 'whatsapp',
            'category' => 'welcome',
        ]);
        $this->assertDatabaseHas('lead_communications', [
            'lead_id' => $lead->id,
            'channel' => 'email',
            'category' => 'welcome',
        ]);
    }

    public function test_calendly_webhook_marks_matching_lead_as_scheduled(): void
    {
        $lead = Lead::create([
            'name' => 'Meeting Lead',
            'email' => 'meeting@example.com',
            'phone' => '+971500000002',
            'consent' => true,
            'source' => 'landing_page',
        ]);

        $this->postJson('/api/webhooks/calendly', [
            'event' => 'invitee.created',
            'payload' => [
                'email' => 'meeting@example.com',
                'uri' => 'https://api.calendly.com/scheduled_events/test/invitees/test',
                'scheduled_event' => [
                    'uri' => 'https://api.calendly.com/scheduled_events/test',
                    'start_time' => now()->addDay()->toIso8601String(),
                ],
            ],
        ])->assertOk()->assertJson(['matched' => true]);

        $lead->refresh();
        $this->assertSame('scheduled', $lead->meeting_status);
        $this->assertSame('booked', $lead->calendly_status);
        $this->assertSame('meeting_scheduled', $lead->status);
        $this->assertNull($lead->next_follow_up_at);
    }
}
