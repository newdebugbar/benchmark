<div class="presence-row" data-testid="traveler-presence">
    <div class="avatar-stack" aria-label="Journey collaborators">
        @foreach ($travelers as $traveler)
            <span class="avatar avatar-{{ $traveler->color }}" title="{{ $traveler->name }}">{{ $traveler->initials }}</span>
        @endforeach
    </div>
    <p><strong>{{ $travelers->where('status', 'Online')->count() + $travelers->where('status', 'Reviewing')->count() }}</strong> here now</p>
</div>
