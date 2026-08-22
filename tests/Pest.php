<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use NewDebugBar\Storage\ProfileStore;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        File::deleteDirectory(storage_path('framework/newdebugbar'));
        $this->seed(DatabaseSeeder::class);
    })
    ->in('Feature', 'Browser');

/** @return array<string, mixed> */
function storedProfile(TestResponse $response): array
{
    $profileId = (string) $response->headers->get('X-NewDebugBar-Profile');

    expect($profileId)->not->toBeEmpty();

    $profile = app(ProfileStore::class)->get($profileId);

    expect($profile)->toBeArray();

    return $profile;
}
