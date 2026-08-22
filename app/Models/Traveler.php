<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A guest or collaborator attached to a journey. */
class Traveler extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
