<?php

namespace App\Models\Product;

use App\Models\Courier as AppCourier;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Courier extends Model
{
    use HasFactory;

    protected $table = 'product_courier';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'courier_id',
        'per_item',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    public function product()
    {
        $this->hasOne(Product::class);
    }

    public function courier()
    {
        return $this->belongsTo(AppCourier::class);
    }
}
