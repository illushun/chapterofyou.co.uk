<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    /** @var array<int, string> */
    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        'line_1',
        'line_2',
        'city',
        'county',
        'postcode',
        'country',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->line_1,
            $this->line_2,
            $this->city,
            $this->county,
            $this->postcode,
            $this->country,
        ];

        return implode(', ', array_filter($parts));
    }
}
