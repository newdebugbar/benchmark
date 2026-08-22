<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Invites a traveler to review a Morrow journey. */
class InviteTravelerController extends Controller
{
    public function __invoke(Request $request, Trip $trip): RedirectResponse
    {
        Gate::authorize('update', $trip);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'role' => ['required', 'in:Lead traveler,Traveler,Guest'],
        ]);

        $trip->travelers()->create([
            ...$validated,
            'initials' => str($validated['name'])->explode(' ')->map(fn (string $part): string => $part[0])->join(''),
            'status' => 'Invited',
            'color' => 'moss',
        ]);

        return to_route('trips.show', $trip)->with('status', 'Traveler invited.');
    }
}
