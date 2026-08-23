<?php

namespace App\Jobs;

use App\Mail\JourneyReviewReady;
use App\Models\Trip;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Sends one real mailable through Laravel's dispatch-after-response lifecycle. */
class SendJourneyReviewAfterResponse implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public readonly int $tripId) {}

    public function handle(): void
    {
        $trip = Trip::query()->findOrFail($this->tripId);

        Log::info('Dispatch-after-response journey review started.', ['trip_id' => $trip->id]);
        Mail::to('theo@example.test')->send(new JourneyReviewReady($trip));
    }
}
