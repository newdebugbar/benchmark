<?php

it('profiles ajax search requests', function (): void {
    $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->getJson('/trips/kyoto-autumn/search?q=garden');

    $response
        ->assertOk()
        ->assertJsonPath('query', 'garden')
        ->assertJsonCount(2, 'results');

    expect(storedProfile($response)['sections']['request']['payload']['request_type'])->toBe('ajax');
});

it('profiles inertia partial reloads', function (): void {
    $version = hash_file('xxh128', public_path('build/manifest.json'));

    $response = $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Inertia-Version' => $version,
        'X-Inertia-Partial-Component' => 'Trips/Map',
        'X-Inertia-Partial-Data' => 'stops',
    ])->get('/trips/kyoto-autumn/map');

    $response
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'Trips/Map')
        ->assertJsonCount(9, 'props.stops');

    expect(storedProfile($response)['sections']['request']['payload']['request_type'])->toBe('json');
});

it('profiles redirects downloads streams and json errors', function (string $uri, string $type, int $status): void {
    $response = $this->get($uri);

    $response->assertStatus($status);

    expect(storedProfile($response)['sections']['request']['payload']['request_type'])->toBe($type);
})->with([
    'redirect' => ['/trips/kyoto-autumn/client-preview', 'redirect', 302],
    'download' => ['/trips/kyoto-autumn/export', 'download', 200],
    'stream' => ['/trips/kyoto-autumn/live', 'stream', 200],
    'json error' => ['/api/trips/kyoto-autumn/availability', 'json', 422],
]);

it('captures validation errors from a real form request', function (): void {
    $response = $this->from('/trips/kyoto-autumn')
        ->post('/trips/kyoto-autumn/travelers', [
            'name' => '',
            'email' => 'not-an-email',
            'role' => 'Owner',
        ]);

    $response
        ->assertRedirect('/trips/kyoto-autumn')
        ->assertSessionHasErrors(['name', 'email', 'role']);

    $profile = storedProfile($response);

    expect($profile['sections']['validation']['summary']['count'])->toBeGreaterThan(0);
});
