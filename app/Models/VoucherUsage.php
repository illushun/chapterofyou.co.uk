<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    protected $table = 'voucher_usage';

    protected $fillable = [
        'voucher_id',
        'user_id',
        'order_id',
        'guest_email',
        'discount_applied',
        'order_total_before',
        'order_total_after',
        'ip_address',
    ];

    protected $casts = [
        'discount_applied'   => 'decimal:2',
        'order_total_before' => 'decimal:2',
        'order_total_after'  => 'decimal:2',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
