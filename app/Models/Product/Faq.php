<?php

namespace App\Models\Product;

use App\Models\Product as ProductModel;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'product_faq';

    protected $fillable = [
        'product_id',
        'question',
        'answer',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
