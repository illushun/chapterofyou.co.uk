<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCartEmail extends Model
{
    protected $table = 'abandoned_cart_email';

    protected $fillable = ['cart_id', 'email', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}
