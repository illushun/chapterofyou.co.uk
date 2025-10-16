<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\OrderItem;
use App\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $table = 'order';

    protected $fillable = [
        'user_id',
        'payment_intent_id',
        'payment_type',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'cost_total',
        'shipping_total',
        'tax_total',
        'grand_total',
        'billing_line_1',
        'billing_line_2',
        'billing_city',
        'billing_county',
        'billing_postcode',
        'billing_country',
        'shipping_line_1',
        'shipping_line_2',
        'shipping_city',
        'shipping_county',
        'shipping_postcode',
        'shipping_country',
        'status',
    ];

    protected $casts = [
        'cost_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => 'string',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
