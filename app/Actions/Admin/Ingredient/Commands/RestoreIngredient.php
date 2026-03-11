<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;

class RestoreIngredient
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $ingredient = Ingredient::onlyTrashed()->findOrFail($id);

        $ingredient->restore();
    }
}
