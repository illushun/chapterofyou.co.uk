<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'product_image';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'image',
        'status',
    ];

    /** @var list<string> */
    protected $hidden = [];
}
