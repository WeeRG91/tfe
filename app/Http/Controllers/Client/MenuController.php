<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\Queries\GetDish;
use App\Actions\Client\Dish\Queries\GetDishes;
use App\Actions\Client\Drink\Queries\GetDrinks;
use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MenuController extends Controller
{
    public function dish(): InertiaResponse
    {
        return Inertia::render('client/Menu', [
            'categories' => DishCategoryEnum::getCategories(),
        ]);
    }

    public function getDishes(Request $request, GetDishes $query): JsonResponse
    {
        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    public function showDish(
        Request $request,
        Dish $dish,
        GetDish $query
    ): InertiaResponse {
        $user = $request->user();

        $existingReview = $user && $dish->ratings()
            ->where('user_id', $user->id)
            ->exists();

        $hasCompletedOrder = $user && ! $existingReview
            ? $user->orders()
                ->where('status', OrderStatusEnum::COMPLETED->value)
                ->whereHas('items', function ($query) use ($dish) {
                    $query
                        ->where('item_type', Dish::class)
                        ->where('item_id', $dish->id);
                })
                ->exists()
            : false;

        return Inertia::render('client/DishDetail', [
            'dish' => $query->execute($dish),
            'reviewEligibility' => [
                'can_review' => $hasCompletedOrder,
                'has_review' => $existingReview,
            ],
        ]);
    }

    public function drink(): InertiaResponse
    {
        return Inertia::render('client/Drinks', [
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    public function getDrinks(Request $request, GetDrinks $query): JsonResponse
    {
        $drinks = $query->execute($request);

        return response()->json($drinks);
    }
}
