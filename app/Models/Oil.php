<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Oil extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'supplier',
        'cas_primary',
    ];

    public function hazards()
    {
        return $this->hasMany(OilHazard::class);
    }

    public function components()
    {
        return $this->hasMany(OilComponent::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_material')
            ->withPivot('percentage')
            ->withTimestamps();
    }
}
