<?php

namespace App\Models\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'product_review';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'user_id',
        'message',
        'rating',
        'status',
        'review_images',
        'admin_reply',
    ];

    /** @var list<string> */
    protected $hidden = [];

    /** @var array */
    protected $casts = [
        'rating' => 'integer',
        'review_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }
}
