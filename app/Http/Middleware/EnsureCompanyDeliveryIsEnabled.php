<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyDeliveryIsEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            config('restaurant.delivery.company.enabled'),
            Response::HTTP_NOT_FOUND,
        );

        return $next($request);
    }
}
