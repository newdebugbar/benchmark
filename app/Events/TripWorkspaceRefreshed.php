<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Announces that fresh travel details are ready for collaborators. */
class TripWorkspaceRefreshed implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $tripId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("trips.{$this->tripId}")];
    }

    public function broadcastAs(): string
    {
        return 'trip.workspace.refreshed';
    }
}
