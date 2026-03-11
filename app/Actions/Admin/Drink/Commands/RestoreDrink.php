<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;

class RestoreDrink
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $drink = Drink::onlyTrashed()->findOrFail($id);

        $drink->restore();
    }
}
