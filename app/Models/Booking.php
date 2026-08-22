<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A reserved or proposed item on a journey. */
class Booking extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'metadata' => 'array'];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(TripDay::class, 'trip_day_id');
    }
}
