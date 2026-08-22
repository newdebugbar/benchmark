<?php

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
        ->and($profile['sections']['queries']['summary']['count'])->toBeGreaterThanOrEqual(20)
        ->and($profile['sections']['http_client']['summary']['count'])->toBe(6)
        ->and($profile['sections']['queue']['summary']['count'])->toBe(3)
        ->and($profile['sections']['mail']['summary']['count'])->toBe(2)
        ->and($profile['sections']['notifications']['summary']['count'])->toBe(2)
        ->and($profile['sections']['redis']['summary']['count'])->toBeGreaterThanOrEqual(5)
        ->and($profile['sections']['validation']['summary']['count'])->toBe(1)
        ->and($profile['sections']['messages']['summary']['count'])->toBe(1)
        ->and($profile['sections']['exceptions']['summary']['count'])->toBe(1)
        ->and($profile['sections']['livewire']['summary']['count'])->toBeGreaterThanOrEqual(3);
});
