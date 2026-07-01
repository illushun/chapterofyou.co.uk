<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalAutoGeneratorSetting extends Model
{
    protected $fillable = [
        'enabled',
        'frequency',
        'day_of_week',
        'day_of_month',
        'topic_notes',
        'last_generated_at',
        'last_run_status',
        'last_error',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
        'last_generated_at' => 'datetime',
    ];

    // Single-row settings table — always returns the one settings record.
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
