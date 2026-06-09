<?php

namespace App\Services;

use App\Models\Lead;

class LeadAutomationService
{
    public function __construct(
        private OpenClawMessenger $messenger,
        private LeadEmailMessenger $email,
        private LeadWelcomeService $welcome,
    ) {
    }

    public function run(): array
    {
        return [
            'pending_welcomes' => $this->sendPendingWelcomes(),
            'daily_guidance' => $this->sendDueDailyGuidance(),
            'follow_ups' => $this->sendDueFollowUps(),
            'reminders' => $this->sendDueMeetingReminders(),
            'admin_notifications' => $this->sendAdminNotifications(),
        ];
    }

    private function sendPendingWelcomes(): int
    {
        $sent = 0;

        Lead::query()
            ->where('consent', true)
            ->whereNotNull('automation_enrolled_at')
            ->where(function ($query): void {
                $query
                    ->where(function ($query): void {
                        $query->whereNotNull('phone')->whereNull('welcome_whatsapp_sent_at');
                    })
                    ->orWhere(function ($query): void {
                        $query->whereNotNull('email')->whereNull('welcome_email_sent_at');
                    });
            })
            ->oldest('automation_enrolled_at')
            ->limit(50)
            ->get()
            ->each(function (Lead $lead) use (&$sent): void {
                $result = $this->welcome->sendPendingWelcome($lead);
                $sent += (int) $result['whatsapp'] + (int) $result['email'];
            });

        return $sent;
    }

    private function sendDueDailyGuidance(): int
    {
        $sent = 0;

        Lead::query()
            ->where('consent', true)
            ->whereNotNull('automation_enrolled_at')
            ->whereNotNull('next_guidance_at')
            ->where('next_guidance_at', '<=', now())
            ->where('guidance_step', '<', 7)
            ->whereNotIn('meeting_status', ['scheduled', 'completed', 'cancelled'])
            ->where('status', '!=', 'converted')
            ->orderBy('next_guidance_at')
            ->limit(50)
            ->get()
            ->each(function (Lead $lead) use (&$sent): void {
                $step = $lead->guidance_step + 1;
                $message = $this->guidanceMessage($lead, $step);
                $hasCalendly = $this->messageHasCalendly($message);
                $subject = $hasCalendly ? 'Your private meeting link' : 'A practical next step for you';
                $delivered = false;

                if ($lead->phone && $this->messenger->send(
                    $lead->phone,
                    $message,
                    $lead,
                    'daily_guidance',
                    "guidance:whatsapp:{$step}",
                )) {
                    $sent++;
                    $delivered = true;
                }

                if ($lead->email && $this->email->send(
                    $lead->email,
                    $subject,
                    $message,
                    $lead,
                    'daily_guidance',
                    "guidance:email:{$step}",
                )) {
                    $sent++;
                    $delivered = true;
                }

                if (! $delivered) {
                    return;
                }

                if ($hasCalendly && $lead->calendly_status !== 'booked') {
                    $lead->calendly_status = 'sent';
                    $lead->status = $lead->status === 'new' ? 'qualified' : $lead->status;
                }

                $lead->guidance_step = $step;
                $lead->last_guidance_at = now();
                $lead->next_guidance_at = $step < 7 ? now()->addDay() : null;
                $lead->save();
            });

        return $sent;
    }

    private function sendDueFollowUps(): int
    {
        $sent = 0;

        Lead::query()
            ->where('consent', true)
            ->whereNotNull('phone')
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now())
            ->where('follow_up_step', '<', 4)
            ->whereNotIn('meeting_status', ['scheduled', 'completed', 'cancelled'])
            ->orderBy('next_follow_up_at')
            ->limit(50)
            ->get()
            ->each(function (Lead $lead) use (&$sent): void {
                $step = $lead->follow_up_step + 1;
                $message = $this->followUpMessage($lead, $step);

                if (! $this->messenger->send(
                    $lead->phone,
                    $message,
                    $lead,
                    'follow_up',
                    "follow-up:whatsapp:{$step}",
                )) {
                    return;
                }

                $lead->follow_up_step = $step;
                $lead->last_follow_up_at = now();
                $lead->next_follow_up_at = match ($step) {
                    1 => now()->addDays(2),
                    2 => now()->addDays(4),
                    3 => now()->addDays(7),
                    default => null,
                };
                $lead->save();
                $sent++;
            });

        return $sent;
    }

    private function sendDueMeetingReminders(): int
    {
        $sent = 0;

        Lead::query()
            ->whereNotNull('phone')
            ->where('meeting_status', 'scheduled')
            ->whereBetween('meeting_scheduled_at', [now()->subMinutes(5), now()->addHours(25)])
            ->get()
            ->each(function (Lead $lead) use (&$sent): void {
                $minutes = now()->diffInMinutes($lead->meeting_scheduled_at, false);
                $key = match (true) {
                    $minutes >= 1425 && $minutes <= 1455 => '24h',
                    $minutes >= 50 && $minutes <= 70 => '1h',
                    $minutes >= 5 && $minutes <= 15 => '10m',
                    default => null,
                };

                $alreadySent = collect($lead->reminders_sent ?? [])->contains($key);
                if (! $key || $alreadySent || ! $this->messenger->send(
                    $lead->phone,
                    $this->reminderMessage($lead, $key),
                    $lead,
                    'meeting_reminder',
                    "reminder:whatsapp:{$key}",
                )) {
                    return;
                }

                $lead->reminders_sent = collect($lead->reminders_sent ?? [])->push($key)->unique()->values()->all();
                $lead->save();
                $sent++;
            });

        return $sent;
    }

    private function sendAdminNotifications(): int
    {
        $adminNumber = config('services.openclaw.admin_whatsapp');
        if (! $adminNumber) {
            return 0;
        }

        $sent = 0;
        Lead::query()
            ->where('updated_at', '>=', now()->subMinutes(15))
            ->latest('updated_at')
            ->limit(100)
            ->get()
            ->each(function (Lead $lead) use ($adminNumber, &$sent): void {
                $stage = match (true) {
                    $lead->meeting_status === 'completed' => 'completed',
                    $lead->meeting_status === 'scheduled' => 'scheduled',
                    $lead->status === 'qualified' || in_array($lead->lead_temperature, ['warm', 'hot'], true) => 'qualified',
                    default => 'new',
                };

                $sentStages = collect($lead->admin_notifications_sent ?? []);
                if ($sentStages->contains($stage)) {
                    return;
                }

                $message = "Lead update: {$lead->name} is now {$stage}. Source: {$lead->source_label}.";
                if (! $this->messenger->send(
                    $adminNumber,
                    $message,
                    $lead,
                    'admin_notification',
                    "admin-notification:whatsapp:{$stage}",
                )) {
                    return;
                }

                $lead->admin_notifications_sent = $sentStages->push($stage)->unique()->values()->all();
                $lead->save();
                $sent++;
            });

        return $sent;
    }

    private function followUpMessage(Lead $lead, int $step): string
    {
        $name = $this->firstName($lead);
        $calendly = config('services.calendly.url');

        return match ($step) {
            1 => "Hi {$name}, just checking in. Did you get a chance to think about what you would like to improve first?",
            2 => "Hi {$name}, I was thinking about your request. If you would still like a private conversation, you can choose a time here: {$calendly}",
            3 => "Hi {$name}, hope everything is going well. If you are still exploring options or have questions, I am happy to help.",
            default => "Hi {$name}, just wanted to check in one last time. Whenever the timing feels right, feel free to reach out.",
        };
    }

    private function reminderMessage(Lead $lead, string $key): string
    {
        $name = $this->firstName($lead);

        return match ($key) {
            '24h' => "Hi {$name}, hope you are doing well. Just a quick reminder about your scheduled meeting tomorrow. Looking forward to speaking with you.",
            '1h' => "Hi {$name}, just a friendly reminder that our meeting is coming up shortly. See you soon.",
            default => "Hi {$name}, looking forward to our conversation in a few minutes.",
        };
    }

    private function guidanceMessage(Lead $lead, int $step): string
    {
        $name = $this->firstName($lead);
        $calendly = config('services.calendly.url');
        $interest = $this->interestText($lead);

        return match ($step) {
            1 => "Hi {$name}, I had a look at your details. Since your interest is {$interest}, the best first step is understanding what matters most to you. What would you like to improve first?",
            2 => "Hi {$name}, a simple way to start is to focus on one practical goal, one small habit, and a realistic amount of time each week. That keeps the process clear and pressure-free.",
            3 => "Hi {$name}, if you feel this is worth exploring, the best next step is a short private conversation with Saleh's team. They can understand your situation and guide you personally. You can choose a convenient time here: {$calendly}",
            4 => "Hi {$name}, before your private conversation, it may help to write down your top two questions. Clear questions make the meeting more useful.",
            5 => "Hi {$name}, remember that this is about learning, habits, and practical next steps. No pressure and no guaranteed outcome.",
            6 => "Hi {$name}, consistency matters more than speed. A steady learning routine can help you understand what fits your goals and current commitments.",
            default => "Hi {$name}, if you still want to speak privately, here is the scheduling link again for convenience: {$calendly}",
        };
    }

    private function messageHasCalendly(string $message): bool
    {
        $calendly = (string) config('services.calendly.url');

        return $calendly !== '' && str_contains($message, $calendly);
    }

    private function interestText(Lead $lead): string
    {
        $interest = $lead->lead_category ?: $lead->interest ?: $lead->main_category ?: $lead->lead_subcategory;

        return trim((string) ($interest ?: 'your current goal'));
    }

    private function firstName(Lead $lead): string
    {
        return str((string) ($lead->name ?: 'there'))->before(' ')->toString();
    }
}