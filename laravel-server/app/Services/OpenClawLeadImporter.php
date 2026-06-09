<?php

namespace App\Services;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OpenClawLeadImporter
{
    public function import(): int
    {
        $imported = 0;

        foreach ($this->candidateFiles() as $path) {
            if (! is_file($path) || ! is_readable($path)) {
                continue;
            }

            $handle = fopen($path, 'rb');

            if (! $handle) {
                continue;
            }

            while (($line = fgets($handle)) !== false) {
                $payload = json_decode(trim($line), true);

                if (! is_array($payload)) {
                    continue;
                }

                if ($this->persist($payload)) {
                    $imported++;
                }
            }

            fclose($handle);
        }

        return $imported;
    }

    /**
     * @return array<int, string>
     */
    private function candidateFiles(): array
    {
        $projectRoot = dirname(base_path());

        return array_values(array_unique(array_filter([
            env('OPENCLAW_LEADS_JSONL'),
            storage_path('app/openclaw/leads.jsonl'),
            storage_path('app/private/openclaw/leads.jsonl'),
            $projectRoot.DIRECTORY_SEPARATOR.'openclaw-workflow'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'leads.jsonl',
        ])));
    }

    private function persist(array $payload): bool
    {
        $externalId = $payload['external_id']
            ?? $payload['whatsapp_user_id']
            ?? $payload['thread_id']
            ?? $payload['session_id']
            ?? null;

        if (! $externalId) {
            $identity = implode('|', [
                $payload['source'] ?? 'whatsapp',
                $payload['phone'] ?? $payload['whatsapp_number'] ?? $payload['number'] ?? $payload['from'] ?? '',
                $payload['email'] ?? '',
                $payload['created_at'] ?? '',
            ]);

            $externalId = 'import:'.sha1($identity);
        }

        if (
            Schema::hasTable('lead_import_tombstones')
            && DB::table('lead_import_tombstones')->where('external_id', (string) $externalId)->exists()
        ) {
            return false;
        }

        $data = [
            'name' => $payload['name'] ?? $payload['full_name'] ?? 'Unknown Lead',
            'phone' => $payload['phone'] ?? $payload['whatsapp_number'] ?? $payload['number'] ?? $payload['from'] ?? null,
            'email' => $payload['email'] ?? null,
            'country' => $payload['country'] ?? $payload['city'] ?? null,
            'occupation' => $payload['occupation'] ?? null,
            'interest' => $payload['interest'] ?? $payload['interest_in'] ?? null,
            'message' => $payload['message'] ?? $payload['notes'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'conversation_history' => $this->conversation($payload),
            'consent' => (bool) ($payload['consent'] ?? false),
            'source' => $payload['source'] ?? 'whatsapp',
            'status' => $payload['status'] ?? $payload['stage'] ?? 'new',
            'meeting_status' => $this->meetingStatus($payload['meeting_status'] ?? null),
            'calendly_status' => ! empty($payload['calendly_link_sent']) ? 'sent' : ($payload['calendly_status'] ?? 'not_sent'),
            'meeting_scheduled_at' => $this->date($payload['meeting_time'] ?? $payload['meeting_date_time'] ?? null),
            'external_id' => (string) $externalId,
            'source_detail' => 'openclaw_jsonl_import',
            'last_message_at' => $this->date($payload['last_message_at'] ?? $payload['created_at'] ?? null),
            'metadata' => Arr::except($payload, [
                'name',
                'full_name',
                'phone',
                'whatsapp_number',
                'number',
                'from',
                'email',
                'country',
                'city',
                'occupation',
                'interest',
                'interest_in',
                'message',
                'notes',
                'conversation_history',
                'messages',
                'consent',
                'source',
                'status',
                'stage',
                'meeting_status',
                'calendly_status',
                'calendly_link_sent',
                'meeting_time',
                'meeting_date_time',
                'external_id',
                'whatsapp_user_id',
                'thread_id',
                'session_id',
                'last_message_at',
                'created_at',
            ]),
        ];

        try {
            $lead = Lead::updateOrCreate(['external_id' => $data['external_id']], $data);
            app(LeadScoringService::class)->assess($lead)->save();
            $wasRecentlyCreated = $lead->wasRecentlyCreated;

            $communications = app(LeadCommunicationService::class);
            $communications->ingestConversation(
                $lead,
                $payload['conversation_history'] ?? $payload['messages'] ?? null,
                'whatsapp',
            );

            if (! empty($payload['message'])) {
                $communications->recordInbound(
                    $lead,
                    'whatsapp',
                    (string) $payload['message'],
                    'message',
                    'openclaw-import-message:'.sha1((string) $externalId.'|'.(string) $payload['message']),
                    ['source' => 'openclaw_jsonl_import'],
                    $payload['last_message_at'] ?? $payload['created_at'] ?? null,
                );
            }

            if (! empty($payload['created_at']) && ! $wasRecentlyCreated) {
                return false;
            }

            if (! empty($payload['created_at'])) {
                $lead->created_at = $this->date($payload['created_at']) ?? $lead->created_at;
                $lead->save();
            }

            if ($wasRecentlyCreated) {
                app(GoogleSheetLeadSync::class)->sync($lead);
            }

            return $wasRecentlyCreated;
        } catch (\Throwable $exception) {
            Log::warning('OpenClaw lead import failed.', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function conversation(array $payload): ?string
    {
        $conversation = $payload['conversation_history'] ?? $payload['messages'] ?? null;

        if (is_array($conversation)) {
            return collect($conversation)
                ->map(function ($item): string {
                    if (is_array($item)) {
                        $speaker = $item['role'] ?? $item['speaker'] ?? $item['from'] ?? 'message';
                        $text = $item['text'] ?? $item['content'] ?? $item['message'] ?? json_encode($item, JSON_UNESCAPED_UNICODE);

                        return trim($speaker.': '.$text);
                    }

                    return (string) $item;
                })
                ->filter()
                ->implode("\n");
        }

        return $conversation ? (string) $conversation : null;
    }

    private function date(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function meetingStatus(?string $status): string
    {
        return match ($status) {
            'booked', 'meeting_booked' => 'scheduled',
            'cancelled' => 'cancelled',
            'completed' => 'completed',
            default => 'not_scheduled',
        };
    }
}
