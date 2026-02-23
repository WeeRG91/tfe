<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteIngredient
{
    /**
     * @param Ingredient $ingredient
     * @return void
     * @throws Throwable
     */
    public function execute(Ingredient $ingredient): void
    {
        DB::transaction(function () use ($ingredient) {
            $ingredient->delete();
        });
    }
}
