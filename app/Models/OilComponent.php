<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OilComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'oil_id',
        'name',
        'cas',
        'concentration_min',
        'concentration_max',
        'clp_classification',
    ];

    public function oil()
    {
        return $this->belongsTo(Oil::class);
    }
}
