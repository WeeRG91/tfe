<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\RestaurantAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantAvailabilityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        RestaurantAvailabilityService $availability,
    ): JsonResponse {
        $validated = $request->validate([
            'days' => [
                'sometimes',
                'integer',
                'min:1',
                'max:14',
            ],
        ]);

        $days = $validated['days']
            ?? config('restaurant.pickup.availability_days');

        $now = CarbonImmutable::now(
            config('restaurant.timezone'),
        );

        return response()->json([
            'data' => [
                'current' => [
                    ...$availability->orderingStatusAt($now),
                    'checked_at' => $now->toIso8601String(),
                ],
                'pickup' => $availability->pickupAvailability(
                    $now,
                    $days,
                ),
            ],
        ]);
    }
}
