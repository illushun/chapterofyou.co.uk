<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Product;
use App\Models\Cart as CartObj;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_item';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    /**
     * An item belongs to a cart.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CartObj::class);
    }

    /**
     * An item belongs to a product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
