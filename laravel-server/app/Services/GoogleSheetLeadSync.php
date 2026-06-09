<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetLeadSync
{
    public function sync(Lead $lead): void
    {
        $webhookUrl = config('services.google_sheets.webhook_url');

        if (! $webhookUrl) {
            return;
        }

        $payload = [
            'secret' => config('services.google_sheets.webhook_secret'),
            'external_id' => $lead->external_id,
            'date' => optional($lead->created_at)->timezone(config('app.timezone', 'UTC'))->format('Y-m-d H:i:s'),
            'name' => $lead->name,
            'whatsapp_number' => $lead->phone,
            'email' => $lead->email,
            'country' => $lead->country,
            'interest_in' => $lead->interest,
            'meeting_date_time' => null,
            'status' => $lead->status_label,
            'source' => $lead->source_label,
            'notes' => trim(implode(' ', array_filter([
                $lead->source ? 'Source: ' . $lead->source . '.' : null,
                $lead->message,
            ]))),
        ];

        try {
            $response = Http::timeout(8)->asJson()->post($webhookUrl, $payload);

            if ($response->failed()) {
                Log::warning('Google Sheet lead sync failed.', [
                    'lead_id' => $lead->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Google Sheet lead sync exception.', [
                'lead_id' => $lead->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
