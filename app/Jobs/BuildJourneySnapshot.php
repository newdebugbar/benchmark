<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/** Builds the heavier local snapshot used by the workspace overview. */
class BuildJourneySnapshot implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $tripId) {}

    public function handle(): void
    {
        usleep(610_000);

        Cache::put("morrow:trip:{$this->tripId}:snapshot", 'ready', 300);
    }
}
