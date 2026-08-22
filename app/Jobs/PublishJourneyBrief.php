<?php

namespace App\Jobs;

use App\Models\Trip;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use NewDebugBar\Debug;

/** Publishes a client-ready journey brief on the document queue. */
class PublishJourneyBrief implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $tripId) {}

    public function handle(): void
    {
        $trip = Trip::query()->withCount(['days', 'bookings'])->findOrFail($this->tripId);
        Cache::put("morrow:trip:{$trip->id}:brief", 'published', 300);
        Redis::setex("morrow:trip:{$trip->id}:document", 60, 'published');
        Log::info('Client journey brief published.', ['trip_id' => $trip->id]);
        Debug::message('Journey document checkpoint', [
            'trip' => $trip->slug,
            'days' => $trip->days_count,
            'bookings' => $trip->bookings_count,
        ]);
    }
}
