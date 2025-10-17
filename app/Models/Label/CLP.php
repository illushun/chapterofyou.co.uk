<?php

namespace App\Models\Label;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Product;

class CLP extends Model
{
    use HasFactory;

    protected $table = 'label_clp';

    protected $fillable = [
        'product_id',
        'product_name',
        'concentration_percent',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
        'signal_word',
        'required_pictograms',
        'hazard_statements',
        'precautionary_statements',
        'supplementary_info',
        'ingredients_json',
    ];

    /**
     * The attributes that should be cast.
     * Use 'array' cast for JSON columns in Eloquent.
     */
    protected $casts = [
        'required_pictograms' => 'array',
        'hazard_statements' => 'array',
        'precautionary_statements' => 'array',
        'ingredients_json' => 'array',
        'concentration_percent' => 'float',
    ];

    /**
     * Get the product that owns the CLP label.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
