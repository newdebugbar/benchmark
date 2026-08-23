<?php

use App\Actions\Trips\ExerciseCommunicationLifecycle;
use App\Mail\JourneyReviewDeliveryProbe;
use App\Mail\JourneyReviewReady;
use App\Notifications\JourneyReviewReminder;
use Illuminate\Support\Facades\DB;
use NewDebugBar\Presentation\ProfilePresenter;

it('lets the package identify and store a complete journey profile', function (): void {
    $response = $this->get('/trips/kyoto-autumn');

    $response
        ->assertOk()
        ->assertHeader('X-NewDebugBar-Profile')
        ->assertSee('Kyoto in autumn')
        ->assertSee('Eight days, thoughtfully paced');

    $profile = app(ProfilePresenter::class)->present(storedProfile($response));

    expect($profile['sections'])->toHaveKeys([
        'overview',
        'request',
        'timeline',
        'queries',
        'http_client',
        'queue',
        'mail',
        'notifications',
        'redis',
        'models',
        'cache',
        'views',
        'events',
        'authorization',
        'validation',
        'messages',
        'logs',
        'exceptions',
        'livewire',
    ]);

    foreach (array_keys($profile['sections']) as $section) {
        expect($profile['sections'][$section]['summary']['count'] ?? 1)
            ->toBeGreaterThan(0, "Expected the {$section} section to contain captured activity.");
    }

    expect($profile['sections']['request']['payload']['request_type'])->toBe('full_page')
        ->and($profile['sections']['queries']['summary']['count'])->toBeGreaterThanOrEqual(18)
        ->and($profile['sections']['http_client']['summary']['count'])->toBe(6)
        ->and($profile['sections']['queue']['summary']['count'])->toBe(2)
        ->and($profile['sections']['mail']['summary']['count'])->toBe(2)
        ->and($profile['sections']['notifications']['summary']['count'])->toBe(2)
        ->and($profile['sections']['notifications']['summary']['notification_count'])->toBe(1)
        ->and($profile['sections']['notifications']['summary']['failed_notification_count'])->toBe(1)
        ->and($profile['sections']['redis']['summary']['count'])->toBeGreaterThanOrEqual(5)
        ->and($profile['sections']['validation']['summary']['count'])->toBe(1)
        ->and($profile['sections']['messages']['summary']['count'])->toBe(1)
        ->and($profile['sections']['exceptions']['summary']['count'])->toBe(1)
        ->and($profile['sections']['livewire']['summary']['count'])->toBeGreaterThanOrEqual(3)
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('queues communication work only from the dedicated local scenario', function (): void {
    $response = $this->get('/trips/kyoto-autumn/debug/communications/pending');

    $response
        ->assertOk()
        ->assertSee('Five real database jobs are waiting for a worker.')
        ->assertHeader('X-NewDebugBar-Profile');

    $profile = app(ProfilePresenter::class)->present(storedProfile($response));
    $mail = collect($profile['sections']['mail']['payload']['items'])
        ->filter(fn (array $item): bool => in_array($item['queue'] ?? null, ExerciseCommunicationLifecycle::PENDING_QUEUES, true))
        ->keyBy('queue');
    $notification = collect($profile['sections']['notifications']['payload']['items'])
        ->firstWhere('queue', 'morrow-notifications');

    expect(DB::table('jobs')->whereIn('queue', ExerciseCommunicationLifecycle::PENDING_QUEUES)->count())->toBe(5)
        ->and($mail)->toHaveCount(4)
        ->and($mail['morrow-mail'])
        ->source->toBe(JourneyReviewReady::class)
        ->status->toBe('queued')
        ->and($mail['morrow-delayed'])
        ->source->toBe(JourneyReviewReady::class)
        ->status->toBe('delayed')
        ->and($mail['morrow-retry'])
        ->source->toBe(JourneyReviewDeliveryProbe::class)
        ->and($mail['morrow-failure'])
        ->source->toBe(JourneyReviewDeliveryProbe::class)
        ->and($notification)
        ->notification->toBe(JourneyReviewReminder::class)
        ->channel->toBe('mail')
        ->and(json_encode($profile))->not->toContain(
            'Kyoto journey retry delivery',
            'Intentional first-attempt mail delivery failure.',
            'Intentional terminal mail delivery failure.',
        );
});
