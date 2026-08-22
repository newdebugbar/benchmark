<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/** Attempts to reconcile a rail hold with its booking partner. */
class SyncRailReservation implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $tripId) {}

    public function handle(): never
    {
        throw new RuntimeException('The rail partner could not refresh reservation KYO-441.');
    }
}
