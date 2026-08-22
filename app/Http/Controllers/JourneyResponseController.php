<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Supplies Morrow's preview, export, stream, and availability responses. */
class JourneyResponseController extends Controller
{
    public function preview(Trip $trip): RedirectResponse
    {
        return to_route('trips.show', $trip, status: 302)->with('preview', true);
    }

    public function export(Trip $trip): StreamedResponse
    {
        return response()->streamDownload(function () use ($trip): void {
            echo "{$trip->title}\n{$trip->destination}\n";

            foreach ($trip->days()->get() as $day) {
                echo "\nDay {$day->position}: {$day->title}\n{$day->subtitle}\n";
            }
        }, 'kyoto-journey.txt', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function stream(Trip $trip): StreamedResponse
    {
        return response()->stream(function () use ($trip): void {
            echo "event: journey\n";
            echo 'data: '.json_encode(['trip' => $trip->slug, 'status' => 'ready'], JSON_THROW_ON_ERROR)."\n\n";
        }, 200, ['Content-Type' => 'text/event-stream']);
    }

    public function availability(Trip $trip): JsonResponse
    {
        return response()->json([
            'message' => 'The selected river departure is no longer available.',
            'errors' => ['departure' => ['Choose the 08:40 or 10:20 departure.']],
            'trip' => $trip->slug,
        ], 422);
    }
}
