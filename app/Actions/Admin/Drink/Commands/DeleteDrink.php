<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteDrink
{
    /**
     * @param Drink $drink
     * @return void
     * @throws Throwable
     */
    public function execute(Drink $drink): void
    {
        DB::transaction(function () use ($drink) {
            $drink->delete();
        });
    }
}
