<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\Queries\GetDish;
use App\Actions\Client\Dish\Queries\GetDishes;
use App\Actions\Client\Drink\Queries\GetDrinks;
use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MenuController extends Controller
{
    /**
     * @return InertiaResponse
     */
    public function dish(): InertiaResponse
    {
        return Inertia::render('client/Menu', [
            'categories' => DishCategoryEnum::getCategories(),
        ]);
    }

    /**
     * @param Request $request
     * @param GetDishes $query
     * @return JsonResponse
     */
    public function getDishes(Request $request, GetDishes $query): JsonResponse
    {
        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    /**
     * @param Dish $dish
     * @param GetDish $query
     * @return InertiaResponse
     */
    public function showDish(Dish $dish, GetDish $query): InertiaResponse
    {
        return Inertia::render('client/DishDetail', [
            'dish' => $query->execute($dish),
        ]);
    }

    /**
     * @return InertiaResponse
     */
    public function drink(): InertiaResponse
    {
        return Inertia::render('client/Drinks', [
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    /**
     * @param Request $request
     * @param GetDrinks $query
     * @return JsonResponse
     */
    public function getDrinks(Request $request, GetDrinks $query): JsonResponse
    {
        $drinks = $query->execute($request);

        return response()->json($drinks);
    }
}
