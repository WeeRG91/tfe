<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;

class RestoreDish
{
    public function execute(int $id): void
    {
        $dish = Dish::onlyTrashed()->findOrFail($id);

        $dish->restore();
    }
}
