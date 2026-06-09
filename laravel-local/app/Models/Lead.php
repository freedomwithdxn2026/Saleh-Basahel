<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'occupation',
        'interest',
        'lead_category',
        'lead_subcategory',
        'lead_detail_option',
        'preferred_time_interest',
        'message',
        'notes',
        'conversation_history',
        'last_message_at',
        'automation_enrolled_at',
        'welcome_whatsapp_sent_at',
        'welcome_email_sent_at',
        'next_guidance_at',
        'last_guidance_at',
        'guidance_step',
        'next_follow_up_at',
        'last_follow_up_at',
        'follow_up_step',
        'reminders_sent',
        'admin_notifications_sent',
        'consent',
        'source',
        'status',
        'lead_score',
        'lead_temperature',
        'meeting_status',
        'calendly_status',
        'calendly_event_uri',
        'meeting_scheduled_at',
        'converted_at',
        'external_id',
        'source_detail',
        'metadata',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'last_message_at' => 'datetime',
        'automation_enrolled_at' => 'datetime',
        'welcome_whatsapp_sent_at' => 'datetime',
        'welcome_email_sent_at' => 'datetime',
        'next_guidance_at' => 'datetime',
        'last_guidance_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'last_follow_up_at' => 'datetime',
        'meeting_scheduled_at' => 'datetime',
        'converted_at' => 'datetime',
        'reminders_sent' => 'array',
        'admin_notifications_sent' => 'array',
        'metadata' => 'array',
    ];

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'whatsapp' => 'WhatsApp',
            'website', 'landing_page' => 'Landing Page',
            default => ucfirst((string) $this->source),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return str((string) ($this->status ?: 'new'))->replace('_', ' ')->title()->toString();
    }

    public function getTemperatureLabelAttribute(): string
    {
        return str((string) ($this->lead_temperature ?: 'cold'))->title()->toString();
    }

    public function getMeetingStatusLabelAttribute(): string
    {
        return str((string) ($this->meeting_status ?: 'not_scheduled'))->replace('_', ' ')->title()->toString();
    }

    public function communications(): HasMany
    {
        return $this->hasMany(LeadCommunication::class);
    }
}
