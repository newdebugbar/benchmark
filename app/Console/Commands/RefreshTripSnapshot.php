<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use NewDebugBar\Debug;

/** Refreshes one journey snapshot from the command line. */
class RefreshTripSnapshot extends Command
{
    protected $signature = 'morrow:refresh {trip=kyoto-autumn : Journey slug}';

    protected $description = 'Refresh a Morrow journey snapshot';

    public function handle(): int
    {
        $trip = Trip::query()
            ->withCount(['days', 'bookings', 'travelers'])
            ->where('slug', $this->argument('trip'))
            ->firstOrFail();

        Cache::forget("morrow:trip:{$trip->id}:command-snapshot");
        Cache::remember(
            "morrow:trip:{$trip->id}:command-snapshot",
            300,
            fn (): array => $trip->only('title', 'destination', 'status'),
        );
        Redis::setex("morrow:trip:{$trip->id}:command", 60, 'complete');
        Redis::get("morrow:trip:{$trip->id}:command");
        Log::info('Journey snapshot refreshed from Artisan.', ['trip_id' => $trip->id]);
        Debug::message('Snapshot refreshed', [
            'trip' => $trip->slug,
            'days' => $trip->days_count,
            'bookings' => $trip->bookings_count,
            'travelers' => $trip->travelers_count,
        ]);

        $this->components->info("{$trip->title} is ready.");

        return self::SUCCESS;
    }
}
