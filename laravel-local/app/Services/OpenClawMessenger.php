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
    ): bool
    {
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

        try {
            $process = new Process([
                'sudo',
                '-n',
                '-u',
                $user,
                $sender,
                $target,
                $message,
            ]);
            $process->setTimeout(45);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::warning('OpenClaw WhatsApp send failed.', [
                    'target' => $target,
                    'error' => trim($process->getErrorOutput()),
                ]);
                $this->failLog($communication, trim($process->getErrorOutput()) ?: 'OpenClaw WhatsApp send failed.');
            } else {
                $this->sentLog($communication, [
                    'provider_output' => str(trim($process->getOutput()))->limit(1000)->toString(),
                ]);
            }

            return $process->isSuccessful();
        } catch (Throwable $exception) {
            Log::warning('OpenClaw WhatsApp send exception.', [
                'target' => $target,
                'error' => $exception->getMessage(),
            ]);
            $this->failLog($communication, $exception->getMessage());

            return false;
        }
    }

    private function normalizeTarget(string $target): ?string
    {
        $target = trim($target);
        $digits = preg_replace('/\D+/', '', $target);

        if (! $digits || strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return '+'.$digits;
    }

    private function beginLog(
        ?Lead $lead,
        string $target,
        string $message,
        string $category,
        ?string $externalKey,
    ): ?LeadCommunication {
        return $lead?->exists
            ? $this->communications->beginOutbound(
                $lead,
                'whatsapp',
                $category,
                $message,
                $target,
                externalKey: $externalKey,
            )
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
