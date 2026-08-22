<?php

namespace App\Http\Controllers;

use App\Actions\Trips\RefreshTripWorkspace;
use App\Models\Trip;
use Illuminate\Contracts\View\View;

/** Renders the working journey used by Morrow's travel designers. */
class TripWorkspaceController extends Controller
{
    public function __invoke(Trip $trip, RefreshTripWorkspace $refresh): View
    {
        return view('trips.show', $refresh->handle($trip));
    }
}
