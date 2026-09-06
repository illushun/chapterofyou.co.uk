<?php

namespace App\Models\Label;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CLP extends Model
{
    use HasFactory;

    protected $table = 'label_clp';

    protected $fillable = [
        'product_id', 'product_name', 'supplier_name',
        'supplier_address', 'supplier_phone', 'signal_word',
        'required_pictograms', 'hazard_statements',
        'precautionary_statements', 'supplementary_info', 'ingredients_json',
    ];

    protected $casts = [
        'required_pictograms' => 'array',
        'hazard_statements' => 'array',
        'precautionary_statements' => 'array',
        'ingredients_json' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
