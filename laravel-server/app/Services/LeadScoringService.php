<?php

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    public function assess(Lead $lead): Lead
    {
        $metadata = $lead->metadata ?? [];
        $score = 0;

        $score += $lead->name && $lead->name !== 'Unknown Lead' ? 10 : 0;
        $score += $lead->phone ? 15 : 0;
        $score += $lead->email ? 8 : 0;
        $score += $lead->country ? 8 : 0;
        $score += $lead->occupation ? 8 : 0;
        $score += $lead->interest ? 10 : 0;
        $score += $lead->lead_category ? 8 : 0;
        $score += $lead->lead_subcategory ? 8 : 0;
        $score += $lead->lead_detail_option ? 8 : 0;
        $score += $lead->preferred_time_interest ? 8 : 0;
        $score += $lead->consent ? 10 : 0;
        $score += ! empty($metadata['goals']) ? 8 : 0;
        $score += ! empty($metadata['available_time_per_week']) ? 8 : 0;
        $score += in_array($lead->calendly_status, ['sent', 'booked'], true) ? 5 : 0;
        $score += $lead->meeting_status === 'scheduled' ? 15 : 0;

        $lead->lead_score = min($score, 100);
        $lead->lead_temperature = match (true) {
            $lead->lead_score >= 70 => 'hot',
            $lead->lead_score >= 40 => 'warm',
            default => 'cold',
        };

        if ($lead->status === 'new' && $lead->lead_score >= 40) {
            $lead->status = 'qualified';
        }

        if (
            $lead->consent
            && $lead->phone
            && ! $lead->next_follow_up_at
            && ! in_array($lead->meeting_status, ['scheduled', 'completed', 'cancelled'], true)
            && $lead->follow_up_step < 4
        ) {
            $lead->next_follow_up_at = now()->addDay();
        }

        return $lead;
    }
}
