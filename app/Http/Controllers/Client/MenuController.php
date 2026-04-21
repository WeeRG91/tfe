<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\GetDish;
use App\Actions\Client\Dish\GetDishes;
use App\Actions\Client\Drink\GetDrinks;
use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    /**
     * @return Response
     */
    public function dish()
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
    public function getDishes(Request $request, GetDishes $query)
    {
        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    /**
     * @param Dish $dish
     * @param GetDish $query
     * @return Response
     */
    public function showDish(Dish $dish, GetDish $query)
    {
        return Inertia::render('client/DishDetail', [
            'dish' => $query->execute($dish),
        ]);
    }

    /**
     * @return Response
     */
    public function drink()
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
    public function getDrinks(Request $request, GetDrinks $query)
    {
        $drinks = $query->execute($request);

        return response()->json($drinks);
    }
}
