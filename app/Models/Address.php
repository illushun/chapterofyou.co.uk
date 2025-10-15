<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\User;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get a single string representation of the address.
     */
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
