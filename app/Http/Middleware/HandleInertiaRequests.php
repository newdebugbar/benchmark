<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/** Adds Morrow's shared product context to Inertia responses. */
class HandleInertiaRequests extends Middleware
{
    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'workspace' => [
                'name' => 'Morrow',
                'user' => $request->user()?->only('name', 'email'),
            ],
        ];
    }
}
