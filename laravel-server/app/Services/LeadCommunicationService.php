<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadCommunication;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class LeadCommunicationService
{
    public function beginOutbound(
        Lead $lead,
        string $channel,
        string $category,
        string $body,
        ?string $recipient = null,
        ?string $subject = null,
        ?string $externalKey = null,
        array $metadata = [],
    ): LeadCommunication {
        $communication = LeadCommunication::firstOrNew([
            'lead_id' => $lead->id,
            'channel' => $channel,
            'external_key' => $externalKey ?: $this->key('outbound', $category, $body),
        ]);

        $communication->fill([
            'direction' => 'outbound',
            'category' => $category,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'recipient' => $recipient,
            'failure_reason' => null,
            'failed_at' => null,
            'last_attempt_at' => now(),
            'metadata' => array_merge($communication->metadata ?? [], $metadata),
        ]);
        $communication->attempt_count = ((int) $communication->attempt_count) + 1;
        $communication->save();

        return $communication;
    }

    public function markSent(LeadCommunication $communication, array $metadata = []): void
    {
        $communication->fill([
            'status' => 'sent',
            'sent_at' => now(),
            'failed_at' => null,
            'failure_reason' => null,
            'metadata' => array_merge($communication->metadata ?? [], $metadata),
        ])->save();
    }

    public function markFailed(LeadCommunication $communication, string $reason, array $metadata = []): void
    {
        $communication->fill([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => Str::limit($reason, 2000, ''),
            'metadata' => array_merge($communication->metadata ?? [], $metadata),
        ])->save();
    }

    public function recordInbound(
        Lead $lead,
        string $channel,
        string $body,
        string $category = 'message',
        ?string $externalKey = null,
        array $metadata = [],
        mixed $occurredAt = null,
    ): LeadCommunication {
        $values = [
            'direction' => 'inbound',
            'category' => $category,
            'body' => $body,
            'status' => 'received',
            'received_at' => $this->date($occurredAt) ?? now(),
            'metadata' => $metadata,
        ];

        if (! $externalKey) {
            return $lead->communications()->create(array_merge($values, [
                'channel' => $channel,
            ]));
        }

        return LeadCommunication::updateOrCreate([
            'lead_id' => $lead->id,
            'channel' => $channel,
            'external_key' => $externalKey,
        ], $values);
    }

    public function recordImported(
        Lead $lead,
        string $direction,
        string $channel,
        string $body,
        string $category,
        string $externalKey,
        array $metadata = [],
        mixed $occurredAt = null,
    ): LeadCommunication {
        $direction = in_array($direction, ['inbound', 'outbound'], true) ? $direction : 'system';
        $occurredAt = $this->date($occurredAt) ?? $lead->created_at ?? now();

        return LeadCommunication::updateOrCreate([
            'lead_id' => $lead->id,
            'channel' => $channel,
            'external_key' => $externalKey,
        ], [
            'direction' => $direction,
            'category' => $category,
            'body' => $body,
            'status' => $direction === 'outbound' ? 'sent' : 'received',
            'sent_at' => $direction === 'outbound' ? $occurredAt : null,
            'received_at' => $direction !== 'outbound' ? $occurredAt : null,
            'metadata' => array_merge(['historical' => true], $metadata),
        ]);
    }

    public function ingestConversation(Lead $lead, mixed $conversation, string $channel = 'whatsapp'): int
    {
        if (! $conversation) {
            return 0;
        }

        $items = is_array($conversation)
            ? $conversation
            : $this->parseTranscript((string) $conversation);
        $recorded = 0;

        foreach ($items as $index => $item) {
            $payload = is_array($item) ? $item : ['text' => (string) $item];
            $body = trim((string) ($payload['text'] ?? $payload['content'] ?? $payload['message'] ?? ''));

            if ($body === '') {
                continue;
            }

            $role = strtolower((string) ($payload['role'] ?? $payload['speaker'] ?? $payload['from'] ?? 'user'));
            $direction = in_array($role, ['assistant', 'agent', 'bot', 'openclaw', 'team'], true)
                ? 'outbound'
                : 'inbound';
            $itemChannel = strtolower((string) ($payload['channel'] ?? $channel));
            $sourceId = $payload['id'] ?? $payload['message_id'] ?? $payload['external_id'] ?? null;
            $externalKey = 'conversation:'.($sourceId ?: sha1($index.'|'.json_encode($payload, JSON_UNESCAPED_UNICODE)));

            $this->recordImported(
                $lead,
                $direction,
                $itemChannel,
                $body,
                'conversation',
                $externalKey,
                ['role' => $role],
                $payload['timestamp'] ?? $payload['created_at'] ?? $payload['date'] ?? null,
            );
            $recorded++;
        }

        return $recorded;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseTranscript(string $conversation): array
    {
        $lines = preg_split('/\R/u', trim($conversation)) ?: [];
        $items = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^(user|customer|lead|assistant|agent|bot|openclaw|team)\s*:\s*(.+)$/iu', $line, $matches)) {
                if ($current) {
                    $items[] = $current;
                }

                $current = [
                    'role' => strtolower($matches[1]),
                    'text' => trim($matches[2]),
                ];

                continue;
            }

            if ($current) {
                $current['text'] .= "\n".$line;
            } else {
                $items[] = ['role' => 'user', 'text' => $line];
            }
        }

        if ($current) {
            $items[] = $current;
        }

        return $items;
    }

    private function key(string $direction, string $category, string $body): string
    {
        return implode(':', [$direction, $category, sha1($body)]);
    }

    private function date(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
