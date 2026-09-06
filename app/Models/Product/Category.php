<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'product_category';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'category_id',
    ];

    /** @var list<string> */
    protected $hidden = [];
}
