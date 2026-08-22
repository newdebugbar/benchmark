<?php

use App\Http\Controllers\InviteTravelerController;
use App\Http\Controllers\JourneyResponseController;
use App\Http\Controllers\JourneySearchController;
use App\Http\Controllers\TripMapController;
use App\Http\Controllers\TripWorkspaceController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\UseMorrowWorkspace;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/trips/kyoto-autumn');

Route::middleware([UseMorrowWorkspace::class, HandleInertiaRequests::class])->group(function (): void {
    Route::get('/trips/{trip}', TripWorkspaceController::class)->name('trips.show');
    Route::get('/trips/{trip}/map', TripMapController::class)->name('trips.map');
    Route::get('/trips/{trip}/search', JourneySearchController::class)->name('trips.search');
    Route::post('/trips/{trip}/travelers', InviteTravelerController::class)->name('trips.travelers.store');
    Route::get('/trips/{trip}/client-preview', [JourneyResponseController::class, 'preview'])->name('trips.preview');
    Route::get('/trips/{trip}/export', [JourneyResponseController::class, 'export'])->name('trips.export');
    Route::get('/trips/{trip}/live', [JourneyResponseController::class, 'stream'])->name('trips.stream');
    Route::get('/api/trips/{trip}/availability', [JourneyResponseController::class, 'availability'])->name('trips.availability');
});
