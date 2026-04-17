<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceConnection extends Model
{
    protected $table = 'marketplace_connection';

    protected $fillable = [
        'marketplace',
        'shop_id',
        'shop_name',
        'etsy_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'last_order_import_at',
    ];

    protected $casts = [
        'expires_at'            => 'datetime',
        'last_order_import_at'  => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
