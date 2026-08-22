<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One planned day within a Morrow journey. */
class TripDay extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('starts_at');
    }
}
