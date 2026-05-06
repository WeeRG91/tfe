<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;

class DeleteDish
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
       $dish = Dish::findOrFail($id);

       $dish->delete();
    }
}
