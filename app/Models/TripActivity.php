<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A human-readable change recorded on a journey. */
class TripActivity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'metadata' => 'array'];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
