<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;

class DeleteDrink
{
    public function execute(int $id): void
    {
        $drink = Drink::findOrFail($id);

        $drink->delete();
    }
}
