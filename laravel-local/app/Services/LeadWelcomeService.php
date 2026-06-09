<?php

namespace App\Services;

use App\Models\Lead;

class LeadWelcomeService
{
    public function __construct(
        private OpenClawMessenger $whatsapp,
        private LeadEmailMessenger $email,
    ) {
    }

    public function enroll(Lead $lead): array
    {
        if (! $lead->consent) {
            return ['whatsapp' => false, 'email' => false];
        }

        $lead->automation_enrolled_at ??= now();
        $lead->next_follow_up_at ??= now()->addDays(8);
        $lead->save();

        return $this->sendPendingWelcome($lead);
    }

    public function sendPendingWelcome(Lead $lead): array
    {
        $sent = ['whatsapp' => false, 'email' => false];

        if (! $lead->consent || ! $lead->automation_enrolled_at) {
            return $sent;
        }

        if ($lead->phone && ! $lead->welcome_whatsapp_sent_at) {
            $sent['whatsapp'] = $this->whatsapp->send(
                $lead->phone,
                $this->welcomeMessage($lead),
                $lead,
                'welcome',
                'welcome:whatsapp',
            );

            if ($sent['whatsapp']) {
                $lead->welcome_whatsapp_sent_at = now();
            }
        }

        if ($lead->email && ! $lead->welcome_email_sent_at) {
            $sent['email'] = $this->email->send(
                $lead->email,
                'Welcome, '.$this->firstName($lead).' - your private next step',
                $this->welcomeEmailMessage($lead),
                $lead,
                'welcome',
                'welcome:email',
            );

            if ($sent['email']) {
                $lead->welcome_email_sent_at = now();
            }
        }

        if (($sent['whatsapp'] || $sent['email']) && ! $lead->next_guidance_at) {
            $lead->next_guidance_at = now()->addHours(6);
        }

        $lead->save();

        return $sent;
    }

    private function welcomeMessage(Lead $lead): string
    {
        $name = $this->firstName($lead);
        $interest = $this->interestText($lead);

        return implode("\n\n", array_filter([
            "Hi {$name}, thanks for sharing your details with Saleh Basahel.",
            $interest ? "I noticed your main interest is {$interest}, so I will keep the follow-up focused around that." : null,
            'This is private and no pressure. To guide you properly, what would you most like to improve or understand first?',
        ]));
    }

    private function welcomeEmailMessage(Lead $lead): string
    {
        $name = $this->firstName($lead);
        $interest = $this->interestText($lead);

        return implode("\n\n", array_filter([
            "Hi {$name},",
            'Thanks for sharing your details with Saleh Basahel. We received your request and will follow up privately with guidance that fits your situation.',
            $interest ? "I noticed your main interest is {$interest}, so the next messages will stay focused around that." : null,
            'There is no pressure and no guaranteed outcome. The goal is to help you understand the next practical step before making any decision.',
            'You can reply to this email or message us on WhatsApp whenever you have a question.',
        ]));
    }

    private function interestText(Lead $lead): ?string
    {
        $interest = $lead->lead_category ?: $lead->interest ?: $lead->main_category ?: $lead->lead_subcategory;

        return $interest ? trim((string) $interest) : null;
    }

    private function firstName(Lead $lead): string
    {
        return str((string) ($lead->name ?: 'there'))->before(' ')->toString();
    }
}