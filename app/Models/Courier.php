<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $table = 'courier';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'status',
        'cost',
    ];

    /** @var list<string> */
    protected $hidden = [];
}
