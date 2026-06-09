<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\LeadScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CalendlyWebhookController extends Controller
{
    public function __invoke(Request $request, LeadScoringService $scoring): JsonResponse
    {
        $token = config('services.calendly.webhook_token');
        $provided = $request->bearerToken() ?: $request->query('token');

        if ($token && ! hash_equals($token, (string) $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = (string) $request->input('event');
        $payload = (array) $request->input('payload', []);
        $email = Arr::get($payload, 'email');
        $phone = $this->findAnswer($payload, ['phone', 'whatsapp']);

        if (! $email && ! $phone) {
            return response()->json(['ok' => true, 'matched' => false]);
        }

        $lead = Lead::query()
            ->where(function ($query) use ($email, $phone) {
                if ($email) {
                    $query->where('email', $email);
                }
                if ($phone) {
                    $query->{$email ? 'orWhere' : 'where'}('phone', 'like', '%'.preg_replace('/\D+/', '', $phone).'%');
                }
            })
            ->latest()
            ->first();

        if (! $lead) {
            return response()->json(['ok' => true, 'matched' => false]);
        }

        if ($event === 'invitee.created') {
            $lead->meeting_status = 'scheduled';
            $lead->calendly_status = 'booked';
            $lead->status = 'meeting_scheduled';
            $lead->calendly_event_uri = Arr::get($payload, 'scheduled_event.uri') ?: Arr::get($payload, 'uri');
            $lead->meeting_scheduled_at = Arr::get($payload, 'scheduled_event.start_time');
            $lead->next_follow_up_at = null;
        } elseif ($event === 'invitee.canceled') {
            $lead->meeting_status = 'cancelled';
            $lead->calendly_status = 'cancelled';
            $lead->status = 'follow_up_needed';
            $lead->next_follow_up_at = now()->addDay();
        }

        $lead->metadata = array_merge($lead->metadata ?? [], ['calendly_payload' => $payload]);
        $scoring->assess($lead)->save();

        return response()->json(['ok' => true, 'matched' => true, 'lead_id' => $lead->id]);
    }

    private function findAnswer(array $payload, array $needles): ?string
    {
        foreach ((array) Arr::get($payload, 'questions_and_answers', []) as $answer) {
            $question = strtolower((string) ($answer['question'] ?? ''));
            foreach ($needles as $needle) {
                if (str_contains($question, $needle)) {
                    return (string) ($answer['answer'] ?? '');
                }
            }
        }

        return null;
    }
}
