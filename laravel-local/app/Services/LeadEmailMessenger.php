<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadCommunication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadEmailMessenger
{
    public function __construct(private LeadCommunicationService $communications)
    {
    }

    public function isConfigured(): bool
    {
        return ! in_array(config('mail.default'), ['array', 'log'], true)
            && config('mail.from.address')
            && config('mail.from.address') !== 'hello@example.com';
    }

    public function send(
        string $target,
        string $subject,
        string $message,
        ?Lead $lead = null,
        string $category = 'message',
        ?string $externalKey = null,
    ): bool
    {
        $communication = $lead?->exists
            ? $this->communications->beginOutbound(
                $lead,
                'email',
                $category,
                $message,
                $target,
                $subject,
                $externalKey,
            )
            : null;

        if (! $this->isConfigured()) {
            Log::warning('Lead email delivery skipped because a real mail provider is not configured.', [
                'target' => $target,
                'mailer' => config('mail.default'),
                'from' => config('mail.from.address'),
            ]);
            $this->failLog($communication, 'A real email provider is not configured.');

            return false;
        }

        try {
            Mail::raw($message, function ($mail) use ($target, $subject): void {
                $mail->to($target)->subject($subject);
            });
            if ($communication) {
                $this->communications->markSent($communication);
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Lead email send failed.', [
                'target' => $target,
                'error' => $exception->getMessage(),
            ]);
            $this->failLog($communication, $exception->getMessage());

            return false;
        }
    }

    private function failLog(?LeadCommunication $communication, string $reason): void
    {
        if ($communication) {
            $this->communications->markFailed($communication, $reason);
        }
    }
}
