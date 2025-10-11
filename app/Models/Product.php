<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $fillable = [
        'mpn',
        'name',
        'description',
        'status',
        'cost',
        'stock_qty',
        'parent_product_id'
    ];

    protected $hidden = [];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'product_category',
            'product_id',
            'category_id'
        );
    }

    /**
     * Scope a query to filter products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return void
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        // Only show top-level products on the main collection page
        $query->whereNull('parent_product_id');

        // MPN and Name Search
        $query->when($filters['search'] ?? false, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('mpn', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%');
            });
        });

        // Category Filter
        $query->when($filters['categories'] ?? false, function ($query, $categoryIds) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        });

        // Price Range Filter
        $query->when($filters['min_price'] ?? false, function ($query, $minPrice) {
            if (is_numeric($minPrice)) {
                $query->where('cost', '>=', $minPrice);
            }
        });
        $query->when($filters['max_price'] ?? false, function ($query, $maxPrice) {
            if (is_numeric($maxPrice)) {
                $query->where('cost', '<=', $maxPrice);
            }
        });

        // Stock Filter
        $query->when($filters['in_stock'] ?? false, function ($query, $inStock) {
            if ($inStock === 'true' || $inStock === true) {
                $query->where('stock_qty', '>', 0);
            }
        });

        // Status Filter
        $query->where('status', 'enabled');
    }
}
