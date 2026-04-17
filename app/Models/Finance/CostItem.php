<?php

namespace App\Models\Finance;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class CostItem extends Model
{
    protected $table = 'finance_cost_item';

    protected $fillable = [
        'name',
        'category',
        'supplier_name',
        'supplier_url',
        'purchase_price',
        'purchase_qty',
        'notes',
    ];

    protected $appends = ['unit_cost'];

    public function getUnitCostAttribute(): float
    {
        if ($this->purchase_qty <= 0) return 0.0;
        return round((float) $this->purchase_price / $this->purchase_qty, 4);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'finance_product_cost', 'cost_item_id', 'product_id')
                    ->withPivot('qty_per_unit')
                    ->withTimestamps();
    }
}
