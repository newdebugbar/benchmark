<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Opens the fixed local Morrow workspace without an authentication screen. */
class UseMorrowWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        if (! Auth::check()) {
            Auth::login(User::query()->where('email', 'mara@morrow.test')->firstOrFail());
        }

        return $next($request);
    }
}
