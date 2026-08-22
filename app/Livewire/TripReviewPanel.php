<?php

namespace App\Livewire;

use App\Models\Trip;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Lets a travel designer edit the private review note for a journey. */
class TripReviewPanel extends Component
{
    #[Locked]
    public int $tripId;

    public string $note = 'Ask Elise whether she prefers the riverside lunch or the garden room.';

    public bool $shared = false;

    public function mount(int $tripId): void
    {
        $this->tripId = $tripId;
    }

    public function saveNote(): void
    {
        $this->validate(['note' => ['required', 'string', 'min:12', 'max:240']]);
        $this->shared = true;
        $this->dispatch('journey-note-saved', tripId: $this->tripId);
    }

    public function render(): mixed
    {
        return view('livewire.trip-review-panel', [
            'trip' => Trip::query()->findOrFail($this->tripId),
        ]);
    }
}
