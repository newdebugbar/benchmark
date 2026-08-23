<?php

namespace App\Http\Controllers;

use App\Actions\Trips\ExerciseCommunicationLifecycle;
use App\Actions\Trips\RefreshTripWorkspace;
use App\Models\Trip;
use Illuminate\Contracts\View\View;

/** Renders one explicit local-only communication lifecycle scenario on Morrow. */
class CommunicationCoverageController extends Controller
{
    public function __invoke(
        Trip $trip,
        string $scenario,
        RefreshTripWorkspace $refresh,
        ExerciseCommunicationLifecycle $communications,
    ): View {
        $workspace = $refresh->handle($trip);
        $workspace['communicationScenario'] = $communications->handle($trip, $scenario);

        return view('trips.show', $workspace);
    }
}
