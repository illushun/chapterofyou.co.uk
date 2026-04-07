<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReview extends Model
{
    protected $table = 'order_review';

    protected $fillable = [
        'order_id',
        'email',
        'sent',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
