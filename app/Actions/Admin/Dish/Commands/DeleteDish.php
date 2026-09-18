<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;

class DeleteDish
{
    public function execute(int $id): void
    {
        $dish = Dish::findOrFail($id);

        $dish->delete();
    }
}
