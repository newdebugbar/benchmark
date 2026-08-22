<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Traveler;
use App\Models\Trip;
use App\Models\TripActivity;
use App\Models\TripDay;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Seeds the stable Morrow workspace used in local development and tests. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Morrow sample data may only be seeded locally.');
        }

        $owner = User::query()->create([
            'name' => 'Mara Bell',
            'email' => 'mara@morrow.test',
            'email_verified_at' => '2026-07-12 09:00:00',
            'password' => Hash::make('morrow'),
        ]);

        $trip = Trip::query()->create([
            'user_id' => $owner->id,
            'slug' => 'kyoto-autumn',
            'title' => 'Kyoto in autumn',
            'destination' => 'Kyoto, Japan',
            'starts_on' => '2026-10-18',
            'ends_on' => '2026-10-25',
            'status' => 'In review',
            'summary' => 'Eight unhurried days shaped around gardens, craft, and the first turn of the maple leaves.',
            'budget_cents' => 864000,
            'currency' => 'EUR',
            'hero_tone' => 'maple',
            'refreshed_at' => '2026-08-22 14:08:00',
        ]);

        $days = collect([
            ['2026-10-18', 'A quiet arrival', 'Settle into Gion before an early dinner', 'Gion', 'Leave the evening deliberately light after the flight.'],
            ['2026-10-19', 'Temples before the crowds', 'Higashiyama at first light', 'Higashiyama', 'Private car starts at 06:40 from the machiya.'],
            ['2026-10-20', 'The northern gardens', 'Moss, stone, and a long lunch', 'Kita', 'Rain plan swaps the garden walk for the Hosomi Museum.'],
            ['2026-10-21', 'A day in Arashiyama', 'River paths and a tea-room visit', 'Arashiyama', 'The boat reservation needs a final passport check.'],
            ['2026-10-22', 'Craft in the old city', 'Meet the makers behind two family studios', 'Nishijin', 'Bring the indigo reference from the client moodboard.'],
            ['2026-10-23', 'Beyond Kyoto', 'A slow train to Uji and Nara', 'Uji', 'Lunch is held until the tea farm confirms harvest timing.'],
            ['2026-10-24', 'A final open day', 'Choose the pace over breakfast', 'Sakyo', 'Three gentle options are ready in the client portal.'],
            ['2026-10-25', 'Homeward', 'Breakfast, a last walk, and the airport', 'Gion', 'Luggage collection is scheduled for 08:10.'],
        ])->map(fn (array $day, int $index): TripDay => TripDay::query()->create([
            'trip_id' => $trip->id,
            'date' => $day[0],
            'title' => $day[1],
            'subtitle' => $day[2],
            'position' => $index + 1,
            'neighborhood' => $day[3],
            'notes' => $day[4],
        ]));

        foreach ([
            [0, 'flight', 'Air France', 'Paris to Osaka · AF292', 'AF-4K8T2', 'Confirmed', '2026-10-17 23:25:00', '2026-10-18 19:10:00', 'CDG Terminal 2E', 348000, ['seat' => '4A · 4C']],
            [0, 'stay', 'Sowaka', 'Garden suite · 7 nights', 'SWK-8821', 'Confirmed', '2026-10-18 20:30:00', '2026-10-25 08:00:00', '480 Kiyoi-cho, Gion', 312000, ['room' => 'Garden suite']],
            [0, 'transfer', 'MK Taxi', 'Private airport transfer', 'MK-4418', 'Confirmed', '2026-10-18 19:45:00', '2026-10-18 21:15:00', 'Kansai International Airport', 32000, ['vehicle' => 'Toyota Alphard']],
            [1, 'experience', 'Morrow Local', 'Kiyomizu-dera before opening', 'ML-1906', 'Confirmed', '2026-10-19 06:50:00', '2026-10-19 09:15:00', 'Higashiyama', 48000, ['host' => 'Aiko Tanaka']],
            [1, 'dining', 'Gion Maruyama', 'Seasonal kaiseki dinner', 'GM-2010', 'Confirmed', '2026-10-19 19:30:00', '2026-10-19 22:00:00', 'Gion', 54000, ['dietary_note' => 'No shellfish for Elise']],
            [2, 'experience', 'Shoden-ji', 'Private garden visit', 'SD-7712', 'Confirmed', '2026-10-20 09:00:00', '2026-10-20 10:30:00', 'Kita', 26000, ['guide' => 'Kenji Watanabe']],
            [3, 'experience', 'Hozugawa Boat', 'River journey to Arashiyama', null, 'Needs details', '2026-10-21 08:40:00', '2026-10-21 10:40:00', 'Kameoka Station', 18000, ['missing' => 'Elise passport number']],
            [4, 'experience', 'Hosoo', 'Textile atelier visit', 'HS-1022', 'Confirmed', '2026-10-22 10:00:00', '2026-10-22 11:30:00', 'Nishijin', 16000, ['host' => 'Mai Hosoo']],
            [5, 'dining', 'Lurra', 'Farm lunch near Uji', null, 'On hold', '2026-10-23 13:00:00', '2026-10-23 15:00:00', 'Uji', 16000, ['hold_expires' => '2026-08-24 17:00']],
        ] as $booking) {
            Booking::query()->create([
                'trip_id' => $trip->id,
                'trip_day_id' => $days[$booking[0]]->id,
                'type' => $booking[1],
                'provider' => $booking[2],
                'title' => $booking[3],
                'confirmation_code' => $booking[4],
                'status' => $booking[5],
                'starts_at' => $booking[6],
                'ends_at' => $booking[7],
                'location' => $booking[8],
                'price_cents' => $booking[9],
                'metadata' => $booking[10],
            ]);
        }

        foreach ([
            ['Mara Bell', 'MB', 'mara@morrow.test', 'Travel designer', 'Online', '2026-08-22 14:08:00', 'terracotta'],
            ['Elise Martin', 'EM', 'elise@example.test', 'Lead traveler', 'Reviewing', '2026-08-22 14:04:00', 'plum'],
            ['Theo Martin', 'TM', 'theo@example.test', 'Traveler', 'Invited', '2026-08-20 18:22:00', 'moss'],
        ] as $traveler) {
            Traveler::query()->create([
                'trip_id' => $trip->id,
                'name' => $traveler[0],
                'initials' => $traveler[1],
                'email' => $traveler[2],
                'role' => $traveler[3],
                'status' => $traveler[4],
                'last_seen_at' => $traveler[5],
                'color' => $traveler[6],
            ]);
        }

        foreach ([
            ['Elise Martin', 'left a note on', 'Day 4 · A day in Arashiyama', '2026-08-22 14:03:00'],
            ['Mara Bell', 'confirmed', 'Gion Maruyama dinner', '2026-08-22 13:42:00'],
            ['Mara Bell', 'updated', 'Private airport transfer', '2026-08-22 12:18:00'],
            ['Theo Martin', 'joined', 'Kyoto in autumn', '2026-08-21 19:05:00'],
        ] as $activity) {
            TripActivity::query()->create([
                'trip_id' => $trip->id,
                'actor_name' => $activity[0],
                'action' => $activity[1],
                'subject' => $activity[2],
                'occurred_at' => $activity[3],
                'metadata' => [],
            ]);
        }
    }
}
