<?php

namespace App\Models;

use App\Models\Label\CLP;
use App\Models\Product\Courier as ProductCourier;
use App\Models\Product\Material;
use App\Models\Product\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;
use App\Models\Product\Category as ProductCategory;
use App\Models\Product\Image;
use App\Models\Product\Seo;
use App\Models\Product\View as ProductView;
use App\Models\Product\Faq as ProductFaq;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    public const SCENT_FAMILIES = [
        'floral',
        'woody',
        'citrus_fresh',
        'spicy_warm',
        'gourmand_sweet',
        'herbal_green',
    ];

    public const MOOD_TAGS = [
        'relaxing',
        'energising',
        'cosy',
        'romantic',
        'fresh_clean',
        'focus_clarity',
    ];

    public const ROOMS = [
        'bedroom',
        'living_room',
        'bathroom',
        'kitchen',
        'office',
        'hallway_entryway',
    ];

    protected $fillable = [
        'mpn',
        'name',
        'description',
        'details',
        'status',
        'cost',
        'stock_qty',
        'parent_product_id',
        'addon_product_id',
        'how_to_use',
        'scent_families',
        'mood_tags',
        'room_tags',
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

    public function addon()
    {
        return $this->belongsTo(Product::class, 'addon_product_id');
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

    public function getScentFamiliesArrayAttribute(): array
    {
        if (! $this->scent_families) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->scent_families)));
    }

    public function getMoodTagsArrayAttribute(): array
    {
        if (! $this->mood_tags) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->mood_tags)));
    }

    public function getRoomTagsArrayAttribute(): array
    {
        if (! $this->room_tags) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->room_tags)));
    }

    public function courier()
    {
        return $this->hasOne(ProductCourier::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function oils()
    {
        return $this->belongsToMany(Oil::class, 'product_material')
            ->withPivot('percentage')
            ->withTimestamps();
    }

    public function clpLabel()
    {
        return $this->hasOne(CLP::class);
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

    public function faqs()
    {
        return $this->hasMany(ProductFaq::class, 'product_id')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }

    public function etsySetting()
    {
        return $this->hasOne(\App\Models\MarketplaceProductSetting::class)
                    ->where('marketplace', 'etsy');
    }

    public function etsyListing()
    {
        return $this->hasOne(\App\Models\MarketplaceListing::class)
                    ->where('marketplace', 'etsy');
    }

    public function costItems()
    {
        return $this->belongsToMany(\App\Models\Finance\CostItem::class, 'finance_product_cost', 'product_id', 'cost_item_id')
                    ->withPivot('qty_per_unit')
                    ->withTimestamps();
    }

    public function journalPosts()
    {
        return $this->belongsToMany(JournalPost::class, 'journal_post_product')
                    ->withTimestamps();
    }
}
