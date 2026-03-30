<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'minimum_order_value',
        'applies_to_all_products',
        'stackable',
        'new_customers_only',
        'single_use_per_user',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'value'                   => 'decimal:2',
        'minimum_order_value'     => 'decimal:2',
        'applies_to_all_products' => 'boolean',
        'stackable'               => 'boolean',
        'new_customers_only'      => 'boolean',
        'single_use_per_user'     => 'boolean',
        'is_active'               => 'boolean',
        'valid_from'              => 'datetime',
        'valid_until'             => 'datetime',
        'uses_count'              => 'integer',
        'max_uses'                => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'voucher_product');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    // ── Computed helpers ───────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function hasStarted(): bool
    {
        return !$this->valid_from || $this->valid_from->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->uses_count >= $this->max_uses;
    }

    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->max_uses === null) {
            return null;
        }
        return max(0, $this->max_uses - $this->uses_count);
    }
}
