<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Dish\GetDish;
use App\Actions\Client\Dish\GetDishes;
use App\Enums\DishCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Dish\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $dishes = Dish::all();

        return Inertia::render('client/Menu', [
            'categories' => DishCategoryEnum::getCategories(),
            'dishes' => DishResource::collection($dishes),
        ]);
    }

    public function getDishes(Request $request, GetDishes $query)
    {
        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    public function showDish(Dish $dish, GetDish $query)
    {
        return Inertia::render('client/DishDetail', [
            'dish' => $query->execute($dish),
        ]);
    }
}
