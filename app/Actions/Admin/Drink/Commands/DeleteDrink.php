<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;

class DeleteDrink
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $drink = Drink::findOrFail($id);

        $drink->delete();
    }
}
