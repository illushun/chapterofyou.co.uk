<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SDSDocument extends Model
{
    protected $fillable = [
        'oil_id', 'file_path', 'document_hash',
        'version', 'issue_date', 'parsed',
    ];

    protected $casts = [
        'parsed'     => 'boolean',
        'issue_date' => 'date',
    ];

    public function oil()
    {
        return $this->belongsTo(Oil::class);
    }
}
