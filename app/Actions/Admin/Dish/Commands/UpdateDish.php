<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateDish
{
    /**
     * @param Dish $dish
     * @param array $data
     * @param array $ingredientIds
     * @return Dish
     * @throws Throwable
     */
    public function execute(Dish $dish, array $data, array $ingredientIds = []): Dish
    {
        return DB::transaction(function () use ($dish, $data, $ingredientIds) {
            $dish->update($data);

            $dish->ingredients()->sync($ingredientIds);

            $dish->uploadImage();

            return $dish;
        });
    }
}
