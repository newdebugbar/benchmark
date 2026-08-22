<?php

use Livewire\Component;

new class extends Component
{
    public string $status = 'All changes synced';

    public bool $connected = true;
};
?>

<div class="sync-pulse" data-testid="journey-pulse">
    <span @class(['is-connected' => $connected])></span>
    {{ $status }}
</div>
