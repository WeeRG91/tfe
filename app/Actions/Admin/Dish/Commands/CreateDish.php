<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateDish
{
    /**
     * @param array $data
     * @param array $ingredientIds
     * @return Dish
     * @throws Throwable
     */
    public function execute(array $data, array $ingredientIds = []): Dish
    {
        return DB::transaction(function () use ($data, $ingredientIds) {
            $dish = Dish::create($data);

            $dish->ingredients()->sync($ingredientIds);

            $dish->uploadImage();

            return $dish;
        });
    }
}
