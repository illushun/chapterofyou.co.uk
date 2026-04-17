<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductSetting extends Model
{
    protected $table = 'marketplace_product_settings';

    protected $fillable = [
        'product_id',
        'marketplace',
        'enabled',
        'override_title',
        'override_description',
        'override_price',
        'override_tags',
    ];

    protected $casts = [
        'enabled'        => 'boolean',
        'override_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function effectiveTitle(Product $product): string
    {
        return $this->override_title ?: $product->name;
    }

    public function effectiveDescription(Product $product): string
    {
        return $this->override_description ?: strip_tags($product->description ?? $product->name);
    }

    public function effectivePrice(Product $product): float
    {
        return $this->override_price !== null ? (float) $this->override_price : (float) $product->cost;
    }

    public function tagsArray(): array
    {
        if (! $this->override_tags) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $this->override_tags))
        ));
    }
}
