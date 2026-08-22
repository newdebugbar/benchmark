<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Publishes a client-ready journey brief on the document queue. */
class PublishJourneyBrief implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $tripId) {}

    public function handle(): void
    {
        // The worker would render and store the final client document.
    }
}
