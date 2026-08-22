<?php

use App\Http\Controllers\TripWorkspaceController;
use App\Http\Middleware\UseMorrowWorkspace;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/trips/kyoto-autumn');

Route::middleware(UseMorrowWorkspace::class)->group(function (): void {
    Route::get('/trips/{trip}', TripWorkspaceController::class)->name('trips.show');
});
