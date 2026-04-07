<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftVoucherOrder extends Model
{
    protected $table = 'gift_voucher_order';

    protected $fillable = [
        'order_id',
        'voucher_id',
        'delivery_type',
        'amount',
        'recipient_name',
        'recipient_email',
        'personal_message',
        'fulfilled_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'fulfilled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled_at !== null;
    }

    public function isEmail(): bool
    {
        return $this->delivery_type === 'email';
    }

    public function isPhysical(): bool
    {
        return $this->delivery_type === 'physical';
    }
}
