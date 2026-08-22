<?php

namespace App\Listeners;

use App\Events\TripWorkspaceRefreshed;
use Illuminate\Support\Facades\Log;

/** Records a concise operational note when a journey refresh completes. */
class RecordWorkspaceRefresh
{
    public function handle(TripWorkspaceRefreshed $event): void
    {
        Log::info('Journey workspace refreshed', ['trip_id' => $event->tripId]);
    }
}
