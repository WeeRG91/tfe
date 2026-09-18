<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Http\Resources\Admin\Drink\DrinkEditResource;
use App\Models\Drink;

class GetDrinkForEdit
{
    public function execute(Drink $drink): DrinkEditResource
    {
        $drink->load([
            'translations',
            'images',
        ]);

        return new DrinkEditResource($drink);
    }
}
