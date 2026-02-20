<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;

class ToggleDishAvailability
{
    /**
     * @param Dish $dish
     * @return void
     */
    public function execute(Dish $dish): void
    {
        $dish->update([
            'is_available' => !$dish->is_available,
        ]);
    }
}
