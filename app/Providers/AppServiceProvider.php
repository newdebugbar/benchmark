<?php

namespace App\Providers;

use App\Events\TripWorkspaceRefreshed;
use App\Listeners\RecordWorkspaceRefresh;
use App\Models\Trip;
use App\Policies\TripPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

/** Registers the small set of Morrow application bindings. */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Trip::class, TripPolicy::class);
        Event::listen(TripWorkspaceRefreshed::class, RecordWorkspaceRefresh::class);

        if (app()->environment(['local', 'testing'])) {
            Redis::enableEvents();
        }
    }
}
