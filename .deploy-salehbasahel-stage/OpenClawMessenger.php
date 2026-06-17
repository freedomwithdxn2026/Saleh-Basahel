<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadCommunication;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class OpenClawMessenger
{
    public function __construct(private LeadCommunicationService $communications)
    {
    }

    public function send(
        string $target,
        string $message,
        ?Lead $lead = null,
        string $category = 'message',
        ?string $externalKey = null,
    ): bool {
        $communication = $this->beginLog($lead, $target, $message, $category, $externalKey);

        if (! config('services.openclaw.messaging_enabled', false) || PHP_OS_FAMILY === 'Windows') {
            $this->failLog($communication, 'OpenClaw WhatsApp messaging is disabled on this host.');

            return false;
        }

        $target = $this->normalizeTarget($target);

        if (! $target) {
            Log::warning('OpenClaw WhatsApp send skipped because the target number is invalid.');
            $this->failLog($communication, 'The WhatsApp target number is invalid.');

            return false;
        }

        $sender = config('services.openclaw.sender', '/usr/local/bin/saleh-openclaw-send');
        $user = config('services.openclaw.user', 'openclaw');
        $timeout = (int) config('services.openclaw.timeout', 45);
        $maxAttempts = max(1, (int) config('services.openclaw.max_attempts', 2));
        $lastError = 'OpenClaw WhatsApp send failed.';

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $process = new Process(['sudo', '-n', '-u', $user, $sender, $target, $message]);
                $process->setTimeout(max(10, $timeout));
                $process->run();

                if ($process->isSuccessful()) {
                    $this->sentLog($communication, [
                        'attempts' => $attempt,
                        'provider_output' => str(trim($process->getOutput()))->limit(1000)->toString(),
                    ]);

                    return true;
                }

                $lastError = trim($process->getErrorOutput()) ?: trim($process->getOutput()) ?: $lastError;
                Log::warning('OpenClaw WhatsApp send attempt failed.', [
                    'target' => $target,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'error' => $lastError,
                ]);
            } catch (Throwable $exception) {
                $lastError = $exception->getMessage();
                Log::warning('OpenClaw WhatsApp send exception.', [
                    'target' => $target,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'error' => $lastError,
                ]);
            }

            if ($attempt < $maxAttempts) {
                usleep(500000);
            }
        }

        $this->failLog($communication, $lastError);

        return false;
    }

    private function normalizeTarget(string $target): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($target));

        if (! $digits || strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return '+'.$digits;
    }

    private function beginLog(?Lead $lead, string $target, string $message, string $category, ?string $externalKey): ?LeadCommunication
    {
        return $lead?->exists
            ? $this->communications->beginOutbound($lead, 'whatsapp', $category, $message, $target, externalKey: $externalKey)
            : null;
    }

    private function sentLog(?LeadCommunication $communication, array $metadata = []): void
    {
        if ($communication) {
            $this->communications->markSent($communication, $metadata);
        }
    }

    private function failLog(?LeadCommunication $communication, string $reason): void
    {
        if ($communication) {
            $this->communications->markFailed($communication, $reason);
        }
    }
}

