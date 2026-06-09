<?php

namespace App\Services;

use App\Models\Lead;

class LeadCommunicationBackfillService
{
    public function __construct(private LeadCommunicationService $communications)
    {
    }

    public function run(): int
    {
        $before = \App\Models\LeadCommunication::count();

        Lead::query()->orderBy('id')->chunkById(100, function ($leads): void {
            foreach ($leads as $lead) {
                $this->backfillLead($lead);
            }
        });

        return \App\Models\LeadCommunication::count() - $before;
    }

    private function backfillLead(Lead $lead): void
    {
        if ($lead->message) {
            $this->communications->recordImported(
                $lead,
                'inbound',
                $lead->source === 'whatsapp' ? 'whatsapp' : 'website',
                $lead->message,
                'lead_message',
                'historical:lead-message',
                occurredAt: $lead->last_message_at ?? $lead->created_at,
            );
        }

        if ($lead->conversation_history) {
            $this->communications->ingestConversation($lead, $lead->conversation_history, 'whatsapp');
        }

        if ($lead->welcome_whatsapp_sent_at) {
            $this->communications->recordImported(
                $lead,
                'outbound',
                'whatsapp',
                $this->welcomeMessage($lead),
                'welcome',
                'welcome:whatsapp',
                occurredAt: $lead->welcome_whatsapp_sent_at,
            );
        }

        if ($lead->welcome_email_sent_at) {
            $this->communications->recordImported(
                $lead,
                'outbound',
                'email',
                $this->welcomeEmailMessage($lead),
                'welcome',
                'welcome:email',
                occurredAt: $lead->welcome_email_sent_at,
            );
        }

        for ($step = 1; $step <= (int) $lead->guidance_step; $step++) {
            $this->communications->recordImported(
                $lead,
                'outbound',
                'automation',
                $this->guidanceMessage($lead, $step),
                'daily_guidance',
                "historical:guidance:{$step}",
                ['channel_unknown' => true],
                $lead->last_guidance_at,
            );
        }

        for ($step = 1; $step <= (int) $lead->follow_up_step; $step++) {
            $this->communications->recordImported(
                $lead,
                'outbound',
                'whatsapp',
                $this->followUpMessage($lead, $step),
                'follow_up',
                "follow-up:whatsapp:{$step}",
                occurredAt: $lead->last_follow_up_at,
            );
        }

        foreach ($lead->reminders_sent ?? [] as $key) {
            $this->communications->recordImported(
                $lead,
                'outbound',
                'whatsapp',
                $this->reminderMessage($lead, $key),
                'meeting_reminder',
                "reminder:whatsapp:{$key}",
                occurredAt: $lead->meeting_scheduled_at,
            );
        }
    }

    private function welcomeMessage(Lead $lead): string
    {
        $name = $this->firstName($lead);

        return "Hi {$name}, thanks for reaching out to Saleh Basahel. We received your details and will follow up privately with guidance based on your interest. You can reply here anytime with a question.";
    }

    private function welcomeEmailMessage(Lead $lead): string
    {
        $name = $this->firstName($lead);

        return implode("\n\n", [
            "Hi {$name},",
            'Thanks for reaching out to Saleh Basahel. We received your details and will follow up privately with guidance based on your interest.',
            'There is no pressure and no guaranteed outcome. The goal is to help you understand your next practical step.',
            'You can reply to this email or message us on WhatsApp whenever you have a question.',
        ]);
    }

    private function guidanceMessage(Lead $lead, int $step): string
    {
        $name = $this->firstName($lead);
        $calendly = config('services.calendly.url');

        return match ($step) {
            1 => "Hi {$name}, a useful first step is to write down one clear goal you would like to improve. Keeping it specific makes the next step easier.",
            2 => "Hi {$name}, small routines usually work better than big promises. What is one simple habit you could repeat for the next seven days?",
            3 => "Hi {$name}, progress often starts with one practical skill. Communication, consistency, and time management are all strong places to begin.",
            4 => "Hi {$name}, it helps to choose a realistic amount of time each week for learning and personal growth, even if it is only a few focused hours.",
            5 => "Hi {$name}, before making any decision, prepare the questions that matter most to you. Clear questions lead to a more useful private conversation.",
            6 => "Hi {$name}, consistency matters more than speed. A steady learning routine can help you understand what fits your goals and current commitments.",
            default => "Hi {$name}, if you would like clear information and a private conversation, you can choose a convenient time here: {$calendly}",
        };
    }

    private function followUpMessage(Lead $lead, int $step): string
    {
        $name = $this->firstName($lead);

        return match ($step) {
            1 => "Hi {$name}, just checking in. Did you get a chance to think about what we discussed?",
            2 => "Hi {$name}, I was thinking about our previous conversation. Would you still like to learn more?",
            3 => "Hi {$name}, hope everything is going well. If you're still exploring options or have questions, I'm happy to help.",
            default => "Hi {$name}, just wanted to check in one last time. Whenever the timing feels right, feel free to reach out.",
        };
    }

    private function reminderMessage(Lead $lead, string $key): string
    {
        $name = $this->firstName($lead);

        return match ($key) {
            '24h' => "Hi {$name}, hope you're doing well. Just a quick reminder about your scheduled meeting tomorrow. Looking forward to speaking with you.",
            '1h' => "Hi {$name}, just a friendly reminder that our meeting is coming up shortly. See you soon.",
            default => "Hi {$name}, looking forward to our conversation in a few minutes.",
        };
    }

    private function firstName(Lead $lead): string
    {
        return str((string) ($lead->name ?: 'there'))->before(' ')->toString();
    }
}
