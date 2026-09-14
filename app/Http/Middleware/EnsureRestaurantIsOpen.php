<?php

namespace App\Http\Middleware;

use App\Services\RestaurantAvailabilityService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsureRestaurantIsOpen
{
    public function __construct(
        private RestaurantAvailabilityService $availability,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = $this->availability->orderingStatusAt();

        if ($status['accepting_orders']) {
            return $next($request);
        }

        return new JsonResponse([
            'message' => $status['message']
                ?: __('messages.restaurant.closed'),
            'code' => 'restaurant_closed',
            'status' => $status['status'],
        ], Response::HTTP_CONFLICT);
    }
}
