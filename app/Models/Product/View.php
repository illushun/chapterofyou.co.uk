<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;

    protected $table = 'product_view';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'ip_address',
        'views',
    ];

    /** @var list<string> */
    protected $hidden = [];
}
