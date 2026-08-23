<?php

namespace App\Actions\Trips;

use App\Jobs\SendJourneyReviewAfterResponse;
use App\Mail\JourneyReviewDeliveryProbe;
use App\Mail\JourneyReviewReady;
use App\Models\Trip;
use App\Notifications\JourneyReviewReminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/** Runs explicit local-only communication scenarios without affecting normal page views. */
class ExerciseCommunicationLifecycle
{
    public const PENDING_QUEUES = [
        'morrow-mail',
        'morrow-delayed',
        'morrow-notifications',
        'morrow-retry',
        'morrow-failure',
    ];

    /** @return array{scenario: string, message: string} */
    public function handle(Trip $trip, string $scenario): array
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return match ($scenario) {
            'pending' => $this->dispatchPending($trip),
            'after-response' => $this->dispatchAfterResponse($trip),
            default => throw new InvalidArgumentException("Unknown communication scenario [{$scenario}]."),
        };
    }

    /** @return array{scenario: string, message: string} */
    private function dispatchPending(Trip $trip): array
    {
        $this->reset($trip);

        Mail::to('elise@example.test')->queue(
            (new JourneyReviewReady($trip))
                ->onConnection('database')
                ->onQueue('morrow-mail'),
        );

        Mail::to('theo@example.test')->later(
            now()->addMinutes(5),
            (new JourneyReviewReady($trip))
                ->onConnection('database')
                ->onQueue('morrow-delayed'),
        );

        $trip->owner->notify(
            (new JourneyReviewReminder($trip))
                ->onConnection('database')
                ->onQueue('morrow-notifications'),
        );

        Mail::to('elise@example.test')->queue(
            (new JourneyReviewDeliveryProbe($trip, JourneyReviewDeliveryProbe::RETRY_ONCE))
                ->onConnection('database')
                ->onQueue('morrow-retry'),
        );

        Mail::to('elise@example.test')->queue(
            (new JourneyReviewDeliveryProbe($trip, JourneyReviewDeliveryProbe::FAIL))
                ->onConnection('database')
                ->onQueue('morrow-failure'),
        );

        return [
            'scenario' => 'pending',
            'message' => 'Five real database jobs are waiting for a worker.',
        ];
    }

    /** @return array{scenario: string, message: string} */
    private function dispatchAfterResponse(Trip $trip): array
    {
        $tripId = $trip->id;

        defer(function () use ($tripId): void {
            $trip = Trip::query()->findOrFail($tripId);

            Mail::to('elise@example.test')->send(new JourneyReviewReady($trip));
        }, 'morrow-deferred-review-mail');

        SendJourneyReviewAfterResponse::dispatchAfterResponse($tripId)
            ->onConnection('sync')
            ->onQueue('morrow-after-response');

        return [
            'scenario' => 'after-response',
            'message' => 'Deferred and dispatch-after-response mail will run after this page is ready.',
        ];
    }

    private function reset(Trip $trip): void
    {
        DB::table('jobs')->whereIn('queue', self::PENDING_QUEUES)->delete();
        DB::table('failed_jobs')->whereIn('queue', self::PENDING_QUEUES)->delete();
        File::deleteDirectory(JourneyReviewDeliveryProbe::markerDirectory($trip));
    }
}
