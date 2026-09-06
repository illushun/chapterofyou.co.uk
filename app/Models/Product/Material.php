<?php

namespace App\Models\Product;

use App\Models\Oil;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'product_material';

    protected $fillable = [
        'product_id',
        'oil_id',
        'percentage',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function oil()
    {
        return $this->belongsTo(Oil::class);
    }
}
