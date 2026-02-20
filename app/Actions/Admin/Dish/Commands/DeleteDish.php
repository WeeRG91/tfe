<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteDish
{
    /**
     * @param Dish $dish
     * @return void
     * @throws Throwable
     */
    public function execute(Dish $dish): void
    {
       DB::transaction(function () use ($dish) {
           $dish->delete();
       });
    }
}
