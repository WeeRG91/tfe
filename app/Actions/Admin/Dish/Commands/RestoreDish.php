<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;

class RestoreDish
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $dish = Dish::onlyTrashed()->findOrFail($id);

        $dish->restore();
    }
}
