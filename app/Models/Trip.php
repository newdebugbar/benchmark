<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A client journey planned and managed in Morrow. */
class Trip extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'refreshed_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(TripDay::class)->orderBy('position');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('starts_at');
    }

    public function travelers(): HasMany
    {
        return $this->hasMany(Traveler::class)->orderBy('id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TripActivity::class)->latest('occurred_at');
    }
}
