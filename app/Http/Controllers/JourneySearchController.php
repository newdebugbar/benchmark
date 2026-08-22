<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Searches a journey's bookings for the quick command menu. */
class JourneySearchController extends Controller
{
    public function __invoke(Request $request, Trip $trip): JsonResponse
    {
        Gate::authorize('view', $trip);
        $query = (string) $request->string('q')->trim();

        $results = $trip->bookings()
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('provider', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            }))
            ->limit(6)
            ->get(['id', 'title', 'provider', 'status']);

        return response()->json(['query' => $query, 'results' => $results]);
    }
}
