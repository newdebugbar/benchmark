<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Presents a journey's confirmed stops on an interactive Inertia surface. */
class TripMapController extends Controller
{
    public function __invoke(Trip $trip): Response
    {
        Gate::authorize('view', $trip);

        return Inertia::render('Trips/Map', [
            'trip' => $trip->only('slug', 'title', 'destination'),
            'stops' => $trip->bookings()
                ->get()
                ->map(fn ($booking): array => [
                    'id' => $booking->id,
                    'title' => $booking->title,
                    'provider' => $booking->provider,
                    'location' => $booking->location,
                    'time' => $booking->starts_at->format('H:i'),
                    'type' => $booking->type,
                    'status' => $booking->status,
                ]),
        ]);
    }
}
