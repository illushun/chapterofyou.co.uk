<?php

// app/Models/WaitlistEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitlistEntry extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'waitlist_entries';

    // The attributes that are mass assignable
    protected $fillable = [
        'email',
    ];
}