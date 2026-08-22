<article class="day-card" data-testid="itinerary-day">
    <div class="day-date">
        <span>{{ $day->date->format('D') }}</span>
        <strong>{{ $day->date->format('d') }}</strong>
        <small>{{ $day->date->format('M') }}</small>
    </div>
    <div class="day-content">
        <div class="day-title-row">
            <div>
                <p>Day {{ $day->position }} · {{ $day->neighborhood }}</p>
                <h3>{{ $day->title }}</h3>
                <span>{{ $day->subtitle }}</span>
            </div>
            <button type="button" aria-label="Open {{ $day->title }}">
                <svg viewBox="0 0 24 24" fill="none"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        @if ($day->bookings->isNotEmpty())
            <div class="booking-list" @if ($day->position === 1) id="bookings" @endif>
                @foreach ($day->bookings->take(3) as $booking)
                    <div class="booking-row">
                        <span class="booking-icon booking-icon-{{ $booking->type }}">
                            @switch($booking->type)
                                @case('flight')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="m2 16 20-8M10 13 5 9M15 11l1-6M6 15l-2 3 4 1"/></svg>
                                    @break
                                @case('stay')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 19V5M20 19v-8H4v8M7 11V8h5v3M3 19h18"/></svg>
                                    @break
                                @case('dining')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 3v8M4 3v5c0 2 1 3 3 3s3-1 3-3V3M7 11v10M16 3v18M16 3c4 2 4 8 0 10"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/></svg>
                            @endswitch
                        </span>
                        <span class="booking-copy">
                            <strong>{{ $booking->title }}</strong>
                            <small>{{ $booking->starts_at->format('H:i') }} · {{ $booking->provider }}</small>
                        </span>
                        <span @class(['booking-status', 'needs-details' => $booking->status !== 'Confirmed'])>
                            {{ $booking->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="open-space">
                <span>Open time</span>
                <p>{{ $day->notes }}</p>
            </div>
        @endif
    </div>
</article>
