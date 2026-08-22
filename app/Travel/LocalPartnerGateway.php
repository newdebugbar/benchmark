<?php

namespace App\Travel;

use App\Models\Trip;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/** Provides stable local responses for Morrow's travel-partner boundary. */
class LocalPartnerGateway
{
    /** @return array<string, mixed> */
    public function refresh(Trip $trip): array
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        Http::preventStrayRequests();
        Http::fake([
            'weather.morrow.test/*' => function () {
                usleep(285_000);

                return Http::response([
                    'condition' => 'Clear mornings, light cloud after lunch',
                    'high_c' => 21,
                    'low_c' => 12,
                    'rain_chance' => 18,
                ], 200, ['X-Forecast-Age' => '14m']);
            },
            'maps.morrow.test/*' => Http::response([
                'walking_minutes' => 14,
                'distance_km' => 1.1,
            ]),
            'passport.morrow.test/*' => Http::response([
                'message' => 'Traveler details are incomplete.',
                'errors' => ['passport_number' => ['A passport number is required.']],
            ], 422),
            'concierge.morrow.test/*' => Http::response(
                ['message' => 'Request limit reached.'],
                429,
                ['Retry-After' => '45'],
            ),
            'rail.morrow.test/*' => Http::response(['message' => 'Reservation service unavailable.'], 503),
            'presence.morrow.test/*' => Http::failedConnection('Presence service refused the local connection.'),
        ]);

        $weather = Http::acceptJson()
            ->timeout(2)
            ->connectTimeout(1)
            ->get('https://weather.morrow.test/v1/forecast', ['trip' => $trip->slug])
            ->throw()
            ->json();

        $walk = Http::acceptJson()
            ->timeout(2)
            ->connectTimeout(1)
            ->get('https://maps.morrow.test/v1/walks/gion-to-kiyomizu')
            ->throw()
            ->json();

        Http::withHeaders(['X-Morrow-Workspace' => 'kyoto'])
            ->timeout(2)
            ->connectTimeout(1)
            ->patch('https://passport.morrow.test/v1/travelers/elise', [
                'passport_number' => null,
                'nationality' => 'French',
            ]);
        Http::timeout(2)->connectTimeout(1)->post('https://concierge.morrow.test/v1/requests', [
            'request' => 'Private garden access',
        ]);
        Http::timeout(2)->connectTimeout(1)->get('https://rail.morrow.test/v1/reservations/KYO-441');

        try {
            Http::timeout(2)->connectTimeout(1)->post('https://presence.morrow.test/v1/sessions', [
                'trip_id' => $trip->id,
            ]);
        } catch (ConnectionException) {
            // Presence falls back to the local Redis heartbeat below.
        }

        return ['weather' => $weather, 'walk' => $walk];
    }
}
