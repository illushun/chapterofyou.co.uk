<?php

namespace App\Models;

use App\Models\Product\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;
use App\Models\Product\Category as ProductCategory;
use App\Models\Product\Image;
use App\Models\Product\Seo;
use App\Models\Product\View as ProductView;

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

    protected $appends = [
        'total_unique_views',
        'average_rating',
        'approved_reviews_count'
    ];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'product_category',
            'product_id',
            'category_id',
            'id',
            'id'
        );
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function children()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function seo()
    {
        return $this->hasOne(Seo::class);
    }

    public function uniqueViews()
    {
        return $this->hasMany(ProductView::class);
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->approved()->avg('rating') ?? 0.0;
    }

    public function getTotalUniqueViewsAttribute()
    {
        // calculates the total count of unique views for the product
        return $this->uniqueViews()->count();
    }

    public function getApprovedReviewsCountAttribute(): int
    {
        return $this->reviews()->approved()->count();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->approved();
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
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
