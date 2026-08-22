<section class="review-card" data-testid="trip-review-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Private note</p>
            <h2>Before you send</h2>
        </div>
        <span class="save-state" @class(['is-saved' => $shared])>{{ $shared ? 'Saved' : 'Draft' }}</span>
    </div>

    <label class="sr-only" for="review-note">Review note</label>
    <textarea id="review-note" wire:model="note" rows="4"></textarea>
    @error('note') <p class="form-error">{{ $message }}</p> @enderror

    <div class="review-actions">
        <p>Only your planning team can see this.</p>
        <button type="button" wire:click="saveNote" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="saveNote">Save note</span>
            <span wire:loading wire:target="saveNote">Saving…</span>
        </button>
    </div>

    <livewire:traveler-presence :trip-id="$trip->id" :key="'presence-'.$trip->id" />
</section>
