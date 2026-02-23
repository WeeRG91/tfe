<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;

class ToggleDrinkAvailability
{
    /**
     * @param Drink $drink
     * @return void
     */
    public function execute(Drink $drink): void
    {
        $drink->update([
            'is_available' => !$drink->is_available,
        ]);
    }
}
