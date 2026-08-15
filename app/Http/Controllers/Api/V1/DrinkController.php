<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DrinkController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $drinks = Drink::query()
            ->orderBy('id')
            ->get();

        return DrinkResource::collection($drinks);
    }
}
