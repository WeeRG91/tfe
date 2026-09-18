<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Http\Resources\Admin\Dish\DishResource;
use App\Models\Dish;

class ToggleDishAvailability
{
    public function execute(int $id): DishResource
    {
        $dish = Dish::findOrFail($id);

        $dish->update([
            'is_available' => ! $dish->is_available,
        ]);

        return new DishResource($dish);
    }
}
