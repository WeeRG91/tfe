<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\Queries\GetRecommendedDishes;
use App\Actions\Client\Rating\Queries\GetHomepageReviews;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Rating\HomepageReviewResource;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetRecommendedDishes $getRecommendedDishes,
        GetHomepageReviews $getHomepageReviews,
    ): Response {
        return Inertia::render('client/Home', [
            'recommendedDishes' => $getRecommendedDishes->execute()->resolve(),
            'homepageReviews' => HomepageReviewResource::collection($getHomepageReviews->execute())->resolve(),
        ]);
    }
}
