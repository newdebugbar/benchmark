<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

/** Defines what a travel designer may do with a Morrow journey. */
class TripPolicy
{
    public function view(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id;
    }

    public function update(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id;
    }

    public function refund(User $user, Trip $trip): bool
    {
        return false;
    }
}
