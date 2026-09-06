<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OilHazard extends Model
{
    use HasFactory;

    protected $table = 'oil_hazard';

    protected $fillable = [
        'oil_id',
        'hazard_class',
        'category',
        'hazard_code',
        'signal_word',
        'pictogram',
    ];

    public function oil()
    {
        return $this->belongsTo(Oil::class);
    }
}
