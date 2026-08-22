<?php

namespace App\Livewire;

use App\Models\Traveler;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/** Shows the collaborators currently attached to a journey review. */
class TravelerPresence extends Component
{
    #[Reactive]
    public int $tripId;

    #[Locked]
    public string $workspace = 'Client review';

    public function render(): mixed
    {
        return view('livewire.traveler-presence', [
            'travelers' => Traveler::query()->where('trip_id', $this->tripId)->get(),
        ]);
    }
}
