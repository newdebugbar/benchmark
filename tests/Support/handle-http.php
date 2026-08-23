<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use NewDebugBar\Storage\ProfileStore;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$request = Request::create($argv[1] ?? '/', 'GET', server: [
    'HTTP_ACCEPT' => 'text/html',
    'HTTP_HOST' => 'morrow.test',
]);
$response = $kernel->handle($request);
$profileId = $response->headers->get('X-NewDebugBar-Profile');
$contentBeforeTerminate = (string) $response->getContent();
$provisional = is_string($profileId) ? $app->make(ProfileStore::class)->get($profileId) : null;
$kernel->terminate($request, $response);
$contentAfterTerminate = (string) $response->getContent();
$final = is_string($profileId) ? $app->make(ProfileStore::class)->get($profileId) : null;

echo json_encode([
    'status' => $response->getStatusCode(),
    'profile_id' => $profileId,
    'toolbar_injected' => str_contains($contentBeforeTerminate, 'id="newdebugbar"'),
    'provisional_completion_state' => $provisional['completion_state'] ?? null,
    'final_completion_state' => $final['completion_state'] ?? null,
    'provisional_duration_ms' => $provisional['metrics']['duration_ms'] ?? null,
    'final_duration_ms' => $final['metrics']['duration_ms'] ?? null,
    'content_unchanged_during_terminate' => hash_equals(
        hash('sha256', $contentBeforeTerminate),
        hash('sha256', $contentAfterTerminate),
    ),
], JSON_THROW_ON_ERROR);
