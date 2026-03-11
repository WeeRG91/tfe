<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Http\Resources\Admin\Drink\DrinkResource;
use App\Models\Drink;

class ToggleDrinkAvailability
{
    /**
     * @param int $id
     * @return DrinkResource
     */
    public function execute(int $id): DrinkResource
    {
        $drink = Drink::findOrFail($id);

        $drink->update([
            'is_available' => !$drink->is_available,
        ]);

        return new DrinkResource($drink);
    }
}
