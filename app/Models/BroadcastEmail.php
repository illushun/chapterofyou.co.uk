<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastEmail extends Model
{
    protected $table = 'broadcast_email';

    protected $fillable = [
        'sent_by',
        'subject',
        'body',
        'audience',
        'recipient_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Human-readable audience label.
     */
    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            'all'              => 'All customers',
            'customers_only'   => 'Registered customers (non-admin)',
            'ordered_last_90'  => 'Ordered in last 90 days',
            'never_ordered'    => 'Registered but never ordered',
            default            => ucfirst($this->audience),
        };
    }
}
