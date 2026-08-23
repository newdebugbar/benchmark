<?php

use App\Actions\Trips\ExerciseCommunicationLifecycle;
use App\Jobs\SendJourneyReviewAfterResponse;
use App\Mail\JourneyReviewDeliveryProbe;
use App\Mail\JourneyReviewReady;
use App\Notifications\JourneyReviewReminder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NewDebugBar\Presentation\McpProfilePresenter;
use NewDebugBar\Presentation\ProfilePresenter;
use NewDebugBar\Storage\BackgroundActivityStore;
use NewDebugBar\Storage\ProfileStore;
use Symfony\Component\Process\Process;

it('stores an artisan command profile in isolated storage', function (): void {
    $workspace = prepareRuntimeWorkspace();

    try {
        runIsolatedArtisan(['morrow:refresh', 'kyoto-autumn'], $workspace);
        useRuntimeProfileStorage($workspace['profiles']);

        $profile = collect(app(ProfileStore::class)->recent())
            ->first(fn (array $profile): bool => ($profile['profile_type'] ?? null) === 'artisan');

        expect($profile)->toBeArray()
            ->and($profile['sections']['request']['payload']['path'])->toBe('artisan:morrow:refresh')
            ->and($profile['sections']['messages']['summary']['count'])->toBeGreaterThan(0);
    } finally {
        File::deleteDirectory($workspace['root']);
    }
});

it('correlates real database worker attempts with queued mail and notifications', function (): void {
    $workspace = prepareRuntimeWorkspace();

    try {
        $http = runIsolatedHttp('/trips/kyoto-autumn/debug/communications/pending', $workspace);
        useRuntimeProfileStorage($workspace['profiles']);

        $store = app(ProfileStore::class);
        $origin = $store->get($http['profile_id']);
        $initial = app(ProfilePresenter::class)->present($origin);
        $queuedActivity = collect($initial['background_activity']['items']);
        $queuedMail = collect($initial['sections']['mail']['payload']['items'])
            ->filter(fn (array $item): bool => in_array($item['queue'] ?? null, ExerciseCommunicationLifecycle::PENDING_QUEUES, true))
            ->keyBy('queue');
        $queuedNotifications = collect($initial['sections']['notifications']['payload']['items'])
            ->where('queue', 'morrow-notifications');

        expect($http)
            ->status->toBe(200)
            ->toolbar_injected->toBeTrue()
            ->content_unchanged_during_terminate->toBeTrue()
            ->provisional_completion_state->toBe('terminating')
            ->final_completion_state->toBe('complete')
            ->provisional_duration_ms->toBe($http['final_duration_ms'])
            ->and($queuedActivity)->toHaveCount(5)
            ->and($queuedMail)->toHaveCount(4)
            ->and($queuedMail['morrow-mail'])
            ->status->toBe('queued')
            ->source->toBe(JourneyReviewReady::class)
            ->connection->toBe('database')
            ->job_id->toMatch('/^[0-9a-f-]{36}$/')
            ->and($queuedMail['morrow-delayed'])
            ->status->toBe('delayed')
            ->delay_seconds->toBeGreaterThan(0)
            ->source->toBe(JourneyReviewReady::class)
            ->and($queuedMail['morrow-retry'])
            ->source->toBe(JourneyReviewDeliveryProbe::class)
            ->and($queuedMail['morrow-failure'])
            ->source->toBe(JourneyReviewDeliveryProbe::class)
            ->and($queuedNotifications)->toHaveCount(1)
            ->and($queuedNotifications->first())
            ->status->toBe('queued')
            ->notification->toBe(JourneyReviewReminder::class)
            ->channel->toBe('mail')
            ->notifiable_count->toBe(1)
            ->and(runtimeTableCount($workspace['database'], 'jobs', ExerciseCommunicationLifecycle::PENDING_QUEUES))->toBe(5)
            ->and(File::isDirectory($workspace['root'].'/morrow-communication-probes'))->toBeFalse();

        $mcpBefore = app(McpProfilePresenter::class)->list([], 20);
        $mcpOriginBefore = collect($mcpBefore['data']['profiles'])->firstWhere('id', $http['profile_id']);

        expect($mcpOriginBefore)
            ->background_pending->toBeTrue()
            ->background_activity_count->toBe(5)
            ->related_profile_ids->toBe([]);

        runIsolatedArtisan([
            'queue:work',
            'database',
            '--queue=morrow-mail,morrow-notifications,morrow-retry,morrow-failure',
            '--stop-when-empty',
            '--max-jobs=10',
            '--sleep=0',
            '--backoff=0',
            '--no-interaction',
        ], $workspace);

        $profiles = collect($store->recent(20));
        $workers = $profiles
            ->where('profile_type', 'queue')
            ->values();
        $workersByQueue = $workers->groupBy(
            fn (array $profile): ?string => $profile['sections']['request']['payload']['context']['queue'] ?? null,
        );
        $mailWorker = $workersByQueue->get('morrow-mail')->first();
        $notificationWorker = $workersByQueue->get('morrow-notifications')->first();
        $retryWorkers = $workersByQueue->get('morrow-retry');
        $failureWorker = $workersByQueue->get('morrow-failure')->first();

        expect($workers)->toHaveCount(5);

        foreach ($workers as $worker) {
            expect($worker['sections'])->toHaveKeys(['queue', 'queries', 'logs', 'exceptions', 'mail', 'notifications'])
                ->and($worker['sections']['queue']['summary']['count'])->toBe(1);
        }

        expect($mailWorker)
            ->sections->queue->payload->items->{0}->status->toBe('sent')
            ->sections->mail->summary->count->toBe(1)
            ->sections->mail->payload->items->{0}->preview->html->toBeString()
            ->and($notificationWorker)
            ->sections->queue->payload->items->{0}->status->toBe('sent')
            ->sections->notifications->summary->sent_count->toBe(1)
            ->sections->notifications->payload->items->{0}->channel->toBe('mail')
            ->sections->mail->summary->count->toBe(1)
            ->sections->mail->payload->items->{0}->preview->html->toBeString()
            ->and($retryWorkers)->toHaveCount(2)
            ->and($retryWorkers->pluck('sections.queue.payload.items.0.status')->sort()->values()->all())->toBe(['sent', 'waiting'])
            ->and($retryWorkers->pluck('sections.request.payload.context.job_id')->unique()->values()->all())
            ->toBe([$queuedMail['morrow-retry']['job_id']])
            ->and($retryWorkers->sum('sections.logs.summary.count'))->toBeGreaterThanOrEqual(2)
            ->and($retryWorkers->sum('sections.exceptions.summary.count'))->toBe(1)
            ->and($failureWorker)
            ->sections->queue->payload->items->{0}->status->toBe('failed')
            ->sections->queue->payload->items->{0}->will_retry->toBeFalse()
            ->sections->logs->summary->count->toBeGreaterThanOrEqual(1)
            ->sections->exceptions->summary->count->toBe(1)
            ->and(runtimeTableCount($workspace['database'], 'failed_jobs', ['morrow-failure']))->toBe(1)
            ->and(runtimeTableCount($workspace['database'], 'jobs', ['morrow-delayed']))->toBe(1);

        $refreshed = app(ProfilePresenter::class)->present($origin);
        $refreshedMail = collect($refreshed['sections']['mail']['payload']['items'])->keyBy('queue');
        $refreshedNotification = collect($refreshed['sections']['notifications']['payload']['items'])
            ->firstWhere('queue', 'morrow-notifications');
        $mcpAfter = app(McpProfilePresenter::class)->list([], 20);
        $mcpOriginAfter = collect($mcpAfter['data']['profiles'])->firstWhere('id', $http['profile_id']);
        $mcpMail = app(McpProfilePresenter::class)->section($http['profile_id'], 'mail', 0, 20);
        $mcpQueuedMail = collect($mcpMail['data']['payload']['items'])->firstWhere('queue', 'morrow-mail');

        expect($refreshedMail['morrow-mail'])
            ->status->toBe('sent')
            ->worker_profile_id->toBe($mailWorker['id'])
            ->and($refreshedMail['morrow-delayed'])
            ->status->toBe('delayed')
            ->worker_profile_id->toBeNull()
            ->and($refreshedMail['morrow-retry'])
            ->status->toBe('sent')
            ->attempts->toHaveCount(2)
            ->and(array_column($refreshedMail['morrow-retry']['attempts'], 'status'))->toBe(['failed', 'sent'])
            ->and($refreshedMail['morrow-failure'])
            ->status->toBe('failed')
            ->and($refreshedNotification)
            ->status->toBe('sent')
            ->worker_profile_id->toBe($notificationWorker['id'])
            ->and($mcpOriginAfter)
            ->background_pending->toBeTrue()
            ->background_activity_count->toBe(5)
            ->related_profile_ids->toHaveCount(5)
            ->and($mcpQueuedMail['correlation_key'])->toBeString();
    } finally {
        File::deleteDirectory($workspace['root']);
    }
});

it('captures defer and dispatch after response without changing HTTP timing or content', function (): void {
    $workspace = prepareRuntimeWorkspace();

    try {
        $http = runIsolatedHttp('/trips/kyoto-autumn/debug/communications/after-response', $workspace);
        useRuntimeProfileStorage($workspace['profiles']);

        $profile = app(ProfileStore::class)->get($http['profile_id']);
        $afterResponseMail = collect($profile['sections']['mail']['payload']['items'])
            ->where('lifecycle', 'after_response')
            ->values();
        $afterResponseQueue = collect($profile['sections']['queue']['payload']['items'])
            ->where('lifecycle', 'after_response')
            ->values();
        $mcp = app(McpProfilePresenter::class)->list([], 20);
        $mcpSummary = collect($mcp['data']['profiles'])->firstWhere('id', $http['profile_id']);

        expect($http)
            ->status->toBe(200)
            ->toolbar_injected->toBeTrue()
            ->content_unchanged_during_terminate->toBeTrue()
            ->provisional_completion_state->toBe('terminating')
            ->final_completion_state->toBe('complete')
            ->provisional_duration_ms->toBe($http['final_duration_ms'])
            ->and($profile)
            ->completion_state->toBe('complete')
            ->metrics->after_response_duration_ms->toBeGreaterThan(0)
            ->and($afterResponseMail)->toHaveCount(2)
            ->and($afterResponseMail->pluck('status')->unique()->values()->all())->toBe(['sent'])
            ->and($afterResponseQueue)->toHaveCount(1)
            ->and($afterResponseQueue->first())
            ->status->toBe('completed')
            ->job->toBe(SendJourneyReviewAfterResponse::class)
            ->and(runtimeTableCount($workspace['database'], 'jobs', ExerciseCommunicationLifecycle::PENDING_QUEUES))->toBe(0)
            ->and($mcpSummary)
            ->completion_state->toBe('complete')
            ->duration_ms->toBe($http['final_duration_ms']);
    } finally {
        File::deleteDirectory($workspace['root']);
    }
});

/** @return array{root: string, database: string, profiles: string} */
function prepareRuntimeWorkspace(): array
{
    $root = storage_path('framework/morrow-runtime-'.Str::uuid());
    $workspace = [
        'root' => $root,
        'database' => $root.'/database.sqlite',
        'profiles' => $root.'/profiles',
    ];

    File::ensureDirectoryExists($root);
    File::put($workspace['database'], '');
    runIsolatedArtisan(['migrate:fresh', '--seed', '--force', '--no-interaction'], $workspace);
    File::deleteDirectory($workspace['profiles']);

    return $workspace;
}

/** @param list<string> $arguments @param array{root: string, database: string, profiles: string} $workspace */
function runIsolatedArtisan(array $arguments, array $workspace): Process
{
    return runRuntimeProcess([PHP_BINARY, 'artisan', ...$arguments], $workspace);
}

/** @param array{root: string, database: string, profiles: string} $workspace @return array<string, mixed> */
function runIsolatedHttp(string $uri, array $workspace): array
{
    $process = runRuntimeProcess([PHP_BINARY, 'tests/Support/handle-http.php', $uri], $workspace);
    $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    expect($result)->toBeArray()
        ->and($result['profile_id'] ?? null)->toBeString()->not->toBeEmpty();

    return $result;
}

/** @param list<string> $command @param array{root: string, database: string, profiles: string} $workspace */
function runRuntimeProcess(array $command, array $workspace): Process
{
    $process = new Process(
        $command,
        base_path(),
        [
            'APP_ENV' => 'local',
            'APP_KEY' => (string) config('app.key'),
            'APP_URL' => 'http://morrow.test',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $workspace['database'],
            'QUEUE_CONNECTION' => 'database',
            'CACHE_STORE' => 'array',
            'MAIL_MAILER' => 'array',
            'SESSION_DRIVER' => 'array',
            'MORROW_NEWDEBUGBAR_STORAGE_PATH' => $workspace['profiles'],
        ],
    );
    $process->setTimeout(60);
    $process->mustRun();

    return $process;
}

function useRuntimeProfileStorage(string $path): void
{
    config()->set('newdebugbar.storage.path', $path);

    foreach ([ProfileStore::class, BackgroundActivityStore::class, McpProfilePresenter::class] as $service) {
        app()->forgetInstance($service);
    }
}

/** @param list<string> $queues */
function runtimeTableCount(string $database, string $table, array $queues): int
{
    $pdo = new PDO('sqlite:'.$database);
    $placeholders = implode(', ', array_fill(0, count($queues), '?'));
    $statement = $pdo->prepare("select count(*) from {$table} where queue in ({$placeholders})");
    $statement->execute($queues);

    return (int) $statement->fetchColumn();
}
