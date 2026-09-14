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

        $status = $availability->orderingStatusAt($now);

        $nextOpenAt = $status['is_open']
            ? null
            : $availability->nextOpenAt($now);

        return response()->json([
            'data' => [
                'current' => [
                    ...$status,
                    'checked_at' => $now->toIso8601String(),
                    'timezone' => config('restaurant.timezone'),
                    'next_open_at' => $nextOpenAt?->toIso8601String(),
                ],
                'pickup' => $availability->pickupAvailability(
                    $now,
                    $days,
                ),
            ],
        ]);
    }
}
