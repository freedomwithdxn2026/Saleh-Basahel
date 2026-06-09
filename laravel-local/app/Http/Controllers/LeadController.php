<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\GoogleSheetLeadSync;
use App\Services\LeadCommunicationService;
use App\Services\LeadScoringService;
use App\Services\LeadWelcomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LeadController extends Controller
{
    public function store(
        Request $request,
        LeadScoringService $scoring,
        LeadWelcomeService $welcome,
        LeadCommunicationService $communications,
        string $locale = 'en',
    ): JsonResponse|RedirectResponse
    {
        app()->setLocale($locale);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:160'],
            'country' => ['required', 'string', 'max:120'],
            'occupation' => ['required', 'string', 'max:160'],
            'interest' => ['required', 'string', 'max:120'],
            'lead_category' => ['required', 'string', 'max:120'],
            'lead_subcategory' => ['required', 'string', 'max:160'],
            'lead_detail_option' => ['required', 'string', 'max:180'],
            'preferred_time_interest' => ['required', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:1200'],
            'consent' => ['accepted'],
        ]);

        $validated['source'] = 'landing_page';
        $validated['status'] = 'new';
        $validated['source_detail'] = 'landing_page_form';

        $lead = new Lead($validated);
        $scoring->assess($lead)->save();

        $communications->recordInbound(
            $lead,
            'website',
            $lead->message ?: 'Landing-page lead form submitted.',
            'lead_submission',
            'landing-submission:'.$lead->id,
            [
                'interest' => $lead->interest,
                'lead_category' => $lead->lead_category,
                'lead_subcategory' => $lead->lead_subcategory,
                'lead_detail_option' => $lead->lead_detail_option,
                'preferred_time_interest' => $lead->preferred_time_interest,
            ],
            $lead->created_at,
        );
        app(GoogleSheetLeadSync::class)->sync($lead);
        $welcome->enroll($lead);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('site.form.success'),
                'lead_id' => $lead->id,
            ]);
        }

        return back()->with('status', __('site.form.success'));
    }

    public function webhook(
        Request $request,
        LeadScoringService $scoring,
        LeadWelcomeService $welcome,
        LeadCommunicationService $communications,
    ): JsonResponse
    {
        $configuredToken = config('services.leads.webhook_token');

        if ($configuredToken && ! hash_equals($configuredToken, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:160'],
            'country' => ['nullable', 'string', 'max:120'],
            'occupation' => ['nullable', 'string', 'max:160'],
            'interest' => ['nullable', 'string', 'max:120'],
            'interest_in' => ['nullable', 'string', 'max:120'],
            'lead_category' => ['nullable', 'string', 'max:120'],
            'lead_subcategory' => ['nullable', 'string', 'max:160'],
            'lead_detail_option' => ['nullable', 'string', 'max:180'],
            'preferred_time_interest' => ['nullable', 'string', 'max:160'],
            'goals' => ['nullable', 'string', 'max:2000'],
            'available_time_per_week' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:5000'],
            'conversation_history' => ['nullable'],
            'message_id' => ['nullable', 'string', 'max:190'],
            'message_timestamp' => ['nullable', 'date'],
            'consent' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:40'],
            'meeting_status' => ['nullable', 'string', 'max:40'],
            'calendly_status' => ['nullable', 'string', 'max:40'],
            'calendly_link_sent' => ['nullable', 'boolean'],
            'meeting_time' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:40'],
            'source_detail' => ['nullable', 'string', 'max:120'],
            'external_id' => ['nullable', 'string', 'max:160'],
            'whatsapp_user_id' => ['nullable', 'string', 'max:160'],
            'created_at' => ['nullable', 'date'],
        ]);

        $source = $validated['source'] ?? 'whatsapp';
        $phone = $validated['phone'] ?? $validated['whatsapp_number'] ?? null;
        $externalId = $validated['external_id'] ?? $validated['whatsapp_user_id'] ?? null;

        if (! $externalId && $source === 'whatsapp' && $phone) {
            $externalId = 'whatsapp:'.$phone;
        }

        $lead = $externalId
            ? Lead::firstOrNew(['external_id' => $externalId])
            : Lead::query()->when($phone, fn ($query) => $query->where('phone', $phone))->first() ?? new Lead();

        $incoming = [
            'name' => $validated['name'] ?? null,
            'phone' => $phone,
            'email' => $validated['email'] ?? null,
            'country' => $validated['country'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'interest' => $validated['interest'] ?? $validated['interest_in'] ?? null,
            'lead_category' => $validated['lead_category'] ?? null,
            'lead_subcategory' => $validated['lead_subcategory'] ?? null,
            'lead_detail_option' => $validated['lead_detail_option'] ?? null,
            'preferred_time_interest' => $validated['preferred_time_interest'] ?? null,
            'message' => $validated['message'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'meeting_status' => $this->normalizeMeetingStatus($validated['meeting_status'] ?? null),
            'calendly_status' => $request->boolean('calendly_link_sent')
                ? 'sent'
                : ($validated['calendly_status'] ?? null),
            'meeting_scheduled_at' => $validated['meeting_time'] ?? null,
            'status' => $validated['status'] ?? null,
            'source_detail' => $validated['source_detail'] ?? null,
        ];

        foreach ($incoming as $key => $value) {
            if ($value !== null && $value !== '') {
                $lead->{$key} = $value;
            }
        }

        $conversation = $this->normalizeConversation(Arr::get($validated, 'conversation_history'));
        if ($conversation && ! str_contains((string) $lead->conversation_history, $conversation)) {
            $lead->conversation_history = trim(implode("\n\n", array_filter([$lead->conversation_history, $conversation])));
        }

        $lead->name ??= 'Unknown Lead';
        $lead->source = $source;
        $lead->status ??= 'new';
        $lead->external_id = $externalId;
        $lead->source_detail ??= 'lead_webhook';
        $lead->last_message_at = now();
        if ($request->has('consent')) {
            $lead->consent = $request->boolean('consent');
        }
        $lead->metadata = array_merge($lead->metadata ?? [], Arr::except($request->all(), [
                'name',
                'phone',
                'whatsapp_number',
                'email',
                'country',
                'occupation',
                'interest',
                'interest_in',
                'lead_category',
                'lead_subcategory',
                'lead_detail_option',
                'preferred_time_interest',
                'goals',
                'available_time_per_week',
                'message',
                'notes',
                'conversation_history',
                'consent',
                'source',
                'status',
                'meeting_status',
                'calendly_status',
                'calendly_link_sent',
                'meeting_time',
                'external_id',
                'whatsapp_user_id',
                'source_detail',
            ]), array_filter([
                'goals' => $validated['goals'] ?? null,
                'available_time_per_week' => $validated['available_time_per_week'] ?? null,
                'lead_category' => $lead->lead_category,
                'lead_subcategory' => $lead->lead_subcategory,
                'lead_detail_option' => $lead->lead_detail_option,
                'preferred_time_interest' => $lead->preferred_time_interest,
            ]));

        $scoring->assess($lead)->save();

        if ($validated['message'] ?? null) {
            $communications->recordInbound(
                $lead,
                $source === 'whatsapp' ? 'whatsapp' : $source,
                $validated['message'],
                'message',
                ($validated['message_id'] ?? null)
                    ? 'inbound:'.$validated['message_id']
                    : null,
                occurredAt: $validated['message_timestamp'] ?? null,
            );
        }
        $communications->ingestConversation($lead, Arr::get($validated, 'conversation_history'), $source);
        app(GoogleSheetLeadSync::class)->sync($lead);
        $welcome->enroll($lead);

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
        ], 201);
    }

    private function normalizeMeetingStatus(?string $status): ?string
    {
        return match ($status) {
            'booked', 'meeting_booked' => 'scheduled',
            'not-sent', 'link-sent', 'reminder-needed', 'unknown' => 'not_scheduled',
            default => $status,
        };
    }

    private function normalizeConversation(mixed $conversation): ?string
    {
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
}
