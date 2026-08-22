<?php

use Illuminate\Support\Facades\File;
use NewDebugBar\Storage\ProfileStore;
use Symfony\Component\Process\Process;

it('stores an artisan command profile', function (): void {
    $database = prepareRuntimeDatabase();

    try {
        File::deleteDirectory(storage_path('framework/newdebugbar'));

        runIsolatedArtisan(['morrow:refresh', 'kyoto-autumn'], $database);

        $profile = collect(app(ProfileStore::class)->recent())
            ->first(fn (array $profile): bool => ($profile['profile_type'] ?? null) === 'artisan');

        expect($profile)->toBeArray()
            ->and($profile['sections']['request']['payload']['path'])->toBe('artisan:morrow:refresh')
            ->and($profile['sections']['messages']['summary']['count'])->toBeGreaterThan(0);
    } finally {
        File::delete($database);
    }
});

it('stores a queue worker profile', function (): void {
    $database = prepareRuntimeDatabase();

    try {
        runIsolatedArtisan([
            'tinker',
            '--execute=App\\Jobs\\PublishJourneyBrief::dispatch(App\\Models\\Trip::query()->where(\'slug\', \'kyoto-autumn\')->valueOrFail(\'id\'))->onConnection(\'database\')->onQueue(\'documents\');',
        ], $database);

        File::deleteDirectory(storage_path('framework/newdebugbar'));

        runIsolatedArtisan([
            'queue:work',
            'database',
            '--queue=documents',
            '--once',
            '--no-interaction',
        ], $database);

        $profile = collect(app(ProfileStore::class)->recent())
            ->first(fn (array $profile): bool => ($profile['profile_type'] ?? null) === 'queue');

        expect($profile)->toBeArray()
            ->and($profile['sections']['request']['payload']['path'])->toContain('queue:')
            ->and($profile['sections']['messages']['summary']['count'])->toBeGreaterThan(0);
    } finally {
        File::delete($database);
    }
});

function prepareRuntimeDatabase(): string
{
    File::ensureDirectoryExists(storage_path('framework'));

    $database = tempnam(storage_path('framework'), 'morrow-runtime-');

    expect($database)->toBeString();

    runIsolatedArtisan(['migrate:fresh', '--seed', '--force', '--no-interaction'], $database);

    return $database;
}

/** @param list<string> $arguments */
function runIsolatedArtisan(array $arguments, string $database): Process
{
    $process = new Process(
        [PHP_BINARY, 'artisan', ...$arguments],
        base_path(),
        [
            'APP_ENV' => 'local',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $database,
            'QUEUE_CONNECTION' => 'database',
            'CACHE_STORE' => 'array',
            'MAIL_MAILER' => 'array',
            'SESSION_DRIVER' => 'array',
        ],
    );
    $process->setTimeout(45);
    $process->mustRun();

    return $process;
}
