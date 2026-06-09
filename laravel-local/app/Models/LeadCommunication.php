<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCommunication extends Model
{
    protected $fillable = [
        'lead_id',
        'direction',
        'channel',
        'category',
        'subject',
        'body',
        'status',
        'recipient',
        'external_key',
        'failure_reason',
        'attempt_count',
        'metadata',
        'sent_at',
        'received_at',
        'failed_at',
        'last_attempt_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'failed_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function getOccurredAtAttribute()
    {
        return $this->sent_at
            ?? $this->received_at
            ?? $this->failed_at
            ?? $this->last_attempt_at
            ?? $this->created_at;
    }
}
