<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    use HasFactory;

    protected $table = 'product_seo';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'meta_title',
        'meta_description',
        'slug',
    ];

    /** @var list<string> */
    protected $hidden = [];
}
