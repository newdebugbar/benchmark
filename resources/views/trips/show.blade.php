<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#243128">
    <title>{{ $trip->title }} · Morrow</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('morrow-theme') || 'light';
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#workspace">Skip to journey</a>

    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-row">
                <a class="brand" href="{{ route('trips.show', $trip) }}" aria-label="Morrow home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none"><path d="M7 23V9l9 8 9-8v14"/><path d="M7 9l9 14L25 9"/></svg>
                    </span>
                    <span>Morrow</span>
                </a>
                <button class="mobile-menu" type="button" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>

            <nav class="primary-nav" aria-label="Main navigation">
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/></svg>
                    <span>Overview</span>
                </a>
                <a class="is-active" href="{{ route('trips.show', $trip) }}">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 19h16M6 16l3.4-9.5 4.1 6L16 9l2 7H6Z"/></svg>
                    <span>Journeys</span>
                    <span class="nav-count">4</span>
                </a>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2"/><path d="M3 20c.4-4.1 2-6 5-6s4.6 1.9 5 6M14 15c3.6-.8 5.8.9 6 4"/></svg>
                    <span>Travelers</span>
                </a>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                    <span>Library</span>
                </a>
            </nav>

            <div class="sidebar-section">
                <div class="sidebar-label">
                    <span>Recent journeys</span>
                    <button type="button" aria-label="Add journey">+</button>
                </div>
                <a class="journey-link is-current" href="{{ route('trips.show', $trip) }}">
                    <span class="journey-thumb journey-thumb-kyoto"></span>
                    <span><strong>Kyoto</strong><small>Oct 18–25</small></span>
                </a>
                <a class="journey-link" href="#">
                    <span class="journey-thumb journey-thumb-lisbon"></span>
                    <span><strong>Lisbon</strong><small>Nov 6–10</small></span>
                </a>
                <a class="journey-link" href="#">
                    <span class="journey-thumb journey-thumb-namibia"></span>
                    <span><strong>Namibia</strong><small>Mar 2–14</small></span>
                </a>
            </div>

            <div class="sidebar-footer">
                <button class="profile-button" type="button">
                    <span class="avatar avatar-terracotta">MB</span>
                    <span><strong>Mara Bell</strong><small>Travel designer</small></span>
                    <svg viewBox="0 0 24 24" fill="none"><path d="m8 10 4 4 4-4"/></svg>
                </button>
            </div>
        </aside>

        <main class="workspace" id="workspace">
            <header class="topbar">
                <div class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="#">Journeys</a>
                    <span>/</span>
                    <strong>Kyoto in autumn</strong>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme">
                        <svg class="sun-icon" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.41M17.66 6.34l1.41-1.41"/></svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" fill="none"><path d="M20 15.2A8.5 8.5 0 0 1 8.8 4 8.5 8.5 0 1 0 20 15.2Z"/></svg>
                    </button>
                    <button class="icon-button" type="button" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M18 9a6 6 0 1 0-12 0c0 7-3 7-3 8h18c0-1-3-1-3-8ZM10 21h4"/></svg>
                        <span class="notification-dot"></span>
                    </button>
                    <button class="primary-button" type="button">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14"/></svg>
                        New journey
                    </button>
                </div>
            </header>

            <section class="trip-hero" aria-labelledby="trip-title">
                <img src="/images/kyoto-autumn.webp" alt="A moss garden and maple trees beside a Kyoto temple at dawn">
                <div class="hero-shade"></div>
                <div class="hero-topline">
                    <span class="status-badge"><i></i>{{ $trip->status }}</span>
                    <button class="hero-more" type="button" aria-label="Journey actions">
                        <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
                    </button>
                </div>
                <div class="hero-copy">
                    <p class="hero-kicker">A private journey for Elise & Theo</p>
                    <h1 id="trip-title">{{ $trip->title }}</h1>
                    <p>{{ $trip->summary }}</p>
                    <div class="hero-meta">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></svg>
                            {{ $trip->starts_on->format('M j') }}–{{ $trip->ends_on->format('j, Y') }}
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2"/></svg>
                            {{ $trip->destination }}
                        </span>
                    </div>
                </div>
                <div class="hero-collaborators">
                    <div class="avatar-stack">
                        @foreach ($travelers as $traveler)
                            <span class="avatar avatar-{{ $traveler->color }}">{{ $traveler->initials }}</span>
                        @endforeach
                    </div>
                    <span>3 collaborators</span>
                </div>
            </section>

            <nav class="trip-tabs" aria-label="Journey sections">
                <a class="is-active" href="#overview">Overview</a>
                <a href="#itinerary">Itinerary <span>{{ $days->count() }}</span></a>
                <a href="#bookings">Bookings <span>{{ $bookings->count() }}</span></a>
                <a href="#travelers">Travelers</a>
                <a href="#files">Files</a>
            </nav>

            <div class="workspace-grid" id="overview">
                <div class="workspace-main">
                    <section class="panel itinerary-panel" id="itinerary">
                        <div class="section-heading itinerary-heading">
                            <div>
                                <p class="eyebrow">Your itinerary</p>
                                <h2>Eight days, thoughtfully paced</h2>
                                <p>Local time · Japan Standard Time</p>
                            </div>
                            <div class="view-switcher" aria-label="Itinerary view">
                                <button class="is-active" type="button">List</button>
                                <a href="{{ route('trips.map', $trip) }}">Map</a>
                            </div>
                        </div>

                        <div class="day-list">
                            @foreach ($days as $day)
                                @include('trips.partials.day', ['day' => $day])
                            @endforeach
                        </div>
                    </section>
                </div>

                <aside class="workspace-aside">
                    <section class="attention-card">
                        <div class="section-heading compact">
                            <div>
                                <p class="eyebrow">Needs attention</p>
                                <h2>Two details to settle</h2>
                            </div>
                            <span class="attention-count">2</span>
                        </div>
                        <a href="#" class="attention-item">
                            <span class="attention-icon">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v5M12 17h.01M10.3 4.9 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.9a2 2 0 0 0-3.4 0Z"/></svg>
                            </span>
                            <span><strong>Passport details</strong><small>Elise · needed for the river boat</small></span>
                            <svg class="chevron" viewBox="0 0 24 24" fill="none"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                        <a href="#" class="attention-item">
                            <span class="attention-icon is-soft">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M5 7h14l-1 12H6L5 7ZM8 7a4 4 0 0 1 8 0"/></svg>
                            </span>
                            <span><strong>Lunch hold expires</strong><small>Lurra · Monday at 17:00</small></span>
                            <svg class="chevron" viewBox="0 0 24 24" fill="none"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </section>

                    <section class="panel forecast-card">
                        <div class="forecast-top">
                            <div>
                                <p class="eyebrow">Kyoto forecast</p>
                                <h2>{{ $weather['high_c'] }}° <span>/ {{ $weather['low_c'] }}°</span></h2>
                            </div>
                            <div class="weather-mark" aria-hidden="true">
                                <svg viewBox="0 0 64 64" fill="none"><circle cx="23" cy="22" r="10"/><path d="M23 6v5M23 33v5M7 22h5M34 22h5M11.7 10.7l3.6 3.6M30.7 29.7l3.6 3.6"/><path d="M21 48h27a9 9 0 0 0 0-18 14 14 0 0 0-26-2 10 10 0 0 0-1 20Z"/></svg>
                            </div>
                        </div>
                        <p>{{ $weather['condition'] }}</p>
                        <div class="forecast-meta">
                            <span><strong>{{ $weather['rain_chance'] }}%</strong> rain</span>
                            <span><strong>{{ $walk['walking_minutes'] }} min</strong> walk</span>
                            <span><strong>17:18</strong> sunset</span>
                        </div>
                    </section>

                    <section class="panel trip-health">
                        <div class="section-heading compact">
                            <div>
                                <p class="eyebrow">Journey health</p>
                                <h2>Nearly ready to share</h2>
                            </div>
                            <span>82%</span>
                        </div>
                        <div class="progress-track"><i style="width:82%"></i></div>
                        <dl>
                            <div><dt>Confirmed</dt><dd>{{ $bookings->where('status', 'Confirmed')->count() }} bookings</dd></div>
                            <div><dt>Planned budget</dt><dd>€{{ number_format($bookings->sum('price_cents') / 100, 0, '.', ',') }}</dd></div>
                            <div><dt>Client review</dt><dd>Opened today</dd></div>
                        </dl>
                        <livewire:journey-pulse />
                    </section>

                    <livewire:trip-review-panel :trip-id="$trip->id" />

                    @env(['local', 'testing'])
                        <section class="panel communication-scenarios" aria-labelledby="communication-scenarios-title" data-testid="communication-scenarios">
                            <div class="section-heading compact">
                                <div>
                                    <p class="eyebrow">Debug scenarios</p>
                                    <h2 id="communication-scenarios-title">Communication lifecycle</h2>
                                </div>
                            </div>
                            <p>Queue real mail and notifications only when you need to inspect them.</p>
                            <div class="view-switcher" aria-label="Communication lifecycle scenarios">
                                <a href="{{ route('trips.debug.communications', [$trip, 'pending']) }}">Queue work</a>
                                <a href="{{ route('trips.debug.communications', [$trip, 'after-response']) }}">After response</a>
                            </div>
                            @if ($communicationScenario ?? null)
                                <p class="scenario-result" role="status">
                                    <strong>{{ $communicationScenario['scenario'] === 'pending' ? 'Queued' : 'Scheduled' }}</strong>
                                    {{ $communicationScenario['message'] }}
                                </p>
                            @endif
                        </section>
                    @endenv

                    <section class="panel activity-card">
                        <div class="section-heading compact">
                            <div>
                                <p class="eyebrow">Recent activity</p>
                                <h2>What changed</h2>
                            </div>
                            <button class="text-button" type="button">View all</button>
                        </div>
                        <ol class="activity-list">
                            @foreach ($activities as $activity)
                                <li>
                                    <span>{{ collect(explode(' ', $activity->actor_name))->map(fn ($part) => $part[0])->join('') }}</span>
                                    <p><strong>{{ $activity->actor_name }}</strong> {{ $activity->action }} <b>{{ $activity->subject }}</b><small>{{ $activity->occurred_at->diffForHumans(now()->setDate(2026, 8, 22)->setTime(14, 10)) }}</small></p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
