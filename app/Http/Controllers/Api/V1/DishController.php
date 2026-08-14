<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DishResource;
use App\Models\Dish;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DishController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $dishes = Dish::query()
            ->orderBy('id')
            ->get();

        return DishResource::collection($dishes);
    }
}
