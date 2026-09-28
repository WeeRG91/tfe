<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\Queries\GetRecommendedDishes;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetRecommendedDishes $getRecommendedDishes,
    ): Response
    {
        return Inertia::render('client/Home', [
            'recommendedDishes' => $getRecommendedDishes->execute()->resolve(),
        ]);
    }
}
