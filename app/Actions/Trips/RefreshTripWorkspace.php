<?php

namespace App\Actions\Trips;

use App\Events\TripWorkspaceRefreshed;
use App\Jobs\BuildJourneySnapshot;
use App\Jobs\SyncRailReservation;
use App\Mail\JourneyReviewReady;
use App\Models\Trip;
use App\Notifications\JourneyAttentionNeeded;
use App\Travel\LocalPartnerGateway;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use NewDebugBar\Debug;
use NewDebugBar\ProfileManager;
use PDO;
use RuntimeException;
use Throwable;

/** Refreshes the complete local workspace shown to a Morrow travel designer. */
class RefreshTripWorkspace
{
    public function __construct(private readonly LocalPartnerGateway $partners) {}

    /** @return array<string, mixed> */
    public function handle(Trip $trip): array
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        Gate::allows('view', $trip);
        Gate::allows('update', $trip);
        Gate::allows('refund', $trip);

        DB::transaction(function () use ($trip): void {
            $trip->forceFill(['refreshed_at' => now()])->save();
        });

        $days = $trip->days()->get();

        foreach ($days as $day) {
            $day->setRelation('bookings', $day->bookings()->get());
        }

        $partnerState = $this->partners->refresh($trip);

        $this->exerciseCache($trip, $partnerState);
        $this->exerciseRedis($trip);
        $this->captureValidationFailure();
        $this->dispatchJourneyWork($trip);
        $this->sendJourneyMessages($trip);
        $this->runSlowPlanningQuery();

        Debug::message('Client review checkpoint', [
            'trip' => $trip->slug,
            'ready_bookings' => $days->flatMap->bookings->where('status', 'Confirmed')->count(),
            'attention_items' => 2,
        ]);

        TripWorkspaceRefreshed::dispatch($trip->id);

        return [
            'trip' => $trip,
            'days' => $days,
            'bookings' => $days->flatMap->bookings,
            'travelers' => $trip->travelers()->get(),
            'activities' => $trip->activities()->limit(4)->get(),
            'weather' => $partnerState['weather'],
            'walk' => $partnerState['walk'],
        ];
    }

    /** @param array<string, mixed> $partnerState */
    private function exerciseCache(Trip $trip, array $partnerState): void
    {
        foreach (range(1, 5) as $index) {
            $key = "morrow:trip:{$trip->id}:option:{$index}";
            Cache::forget($key);
            Cache::get($key);
        }

        $weatherKey = "morrow:trip:{$trip->id}:weather";
        Cache::put($weatherKey, $partnerState['weather'], 300);
        Cache::get($weatherKey);
        Cache::forget("morrow:trip:{$trip->id}:stale-quote");
    }

    private function exerciseRedis(Trip $trip): void
    {
        try {
            $redis = Redis::connection();
            $heartbeatKey = "morrow:trip:{$trip->id}:heartbeat";
            $presenceKey = "morrow:trip:{$trip->id}:presence";
            $redis->setex($heartbeatKey, 60, 'active');
            $redis->get($heartbeatKey);
            $redis->hset($presenceKey, 'elise', 'reviewing');
            $redis->hgetall($presenceKey);
            $redis->del($heartbeatKey, $presenceKey);
        } catch (Throwable $exception) {
            Log::notice('Live collaborator presence is unavailable.', ['exception' => $exception]);
        }
    }

    private function captureValidationFailure(): void
    {
        try {
            Validator::make([
                'passport_number' => '',
                'contact_email' => 'elise-at-example.test',
            ], [
                'passport_number' => ['required', 'string'],
                'contact_email' => ['required', 'email'],
            ])->validateWithBag('traveler-details');
        } catch (ValidationException $exception) {
            app(ProfileManager::class)->recordValidationException($exception);
        }
    }

    private function dispatchJourneyWork(Trip $trip): void
    {
        Bus::dispatchSync(new BuildJourneySnapshot($trip->id));

        try {
            Bus::dispatchSync(new SyncRailReservation($trip->id));
        } catch (RuntimeException $exception) {
            Log::warning('Rail reservation refresh needs attention.', [
                'trip_id' => $trip->id,
                'exception' => $exception,
            ]);
        }
    }

    private function sendJourneyMessages(Trip $trip): void
    {
        Mail::to('elise@example.test')
            ->cc('mara@morrow.test')
            ->send(new JourneyReviewReady($trip));

        $notification = new JourneyAttentionNeeded($trip);
        $notification->id = (string) Str::uuid();
        $trip->owner->notify($notification);
        Event::dispatch(new NotificationFailed(
            $trip->owner,
            $notification,
            'sms',
            ['reason' => 'Traveler phone number is not verified.'],
        ));
    }

    private function runSlowPlanningQuery(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        $pdo = $connection->getPdo();

        if (! $pdo instanceof PDO || ! method_exists($pdo, 'sqliteCreateFunction')) {
            return;
        }

        $pdo->sqliteCreateFunction('morrow_pause', static function (): int {
            usleep(125_000);

            return 1;
        });

        DB::select('select morrow_pause() as itinerary_ready');
    }
}
