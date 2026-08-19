<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

#[Fillable([
    'campaign_id',
    'recipient_id',
    'phone_number',
    'personalized_message',
    'ultramsg_message_id',
    'status',
    'error_message',
    'sent_at'
])]
class Message extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the campaign that owns the message.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the recipient that owns the message.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    /**
     * Scope a query to only include sent messages.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'delivered', 'read']);
    }

    /**
     * Scope a query to only include failed messages.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending messages.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'queued']);
    }
}
