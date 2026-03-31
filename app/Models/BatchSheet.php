<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchSheet extends Model
{
    protected $table = 'batch_sheets';

    protected $fillable = [
        'order_id',
        'product_id',
        'created_by',
        'batch_number',
        'blend_name',
        'date_of_manufacture',
        'produced_by',
        'bottle_size_ml',
        'total_units_produced',
        'ingredients',
        'ifra_certificate_checked',
        'max_percent_allowed',
        'sds_hazards_noted',
        'clp_label_prepared',
        'notes',
    ];

    protected $casts = [
        'ingredients'              => 'array',
        'ifra_certificate_checked' => 'boolean',
        'clp_label_prepared'       => 'boolean',
        'date_of_manufacture'      => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
