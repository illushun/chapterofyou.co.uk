<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    use HasFactory;

    protected $table = 'address_lookup';

    /** @var array<int, string> */
    protected $fillable = [
        'query',
        'data',
    ];

    /** @var array */
    protected $casts = [
        'data' => 'array',
    ];
}
