<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;

class RestoreIngredient
{
    public function execute(int $id): void
    {
        $ingredient = Ingredient::onlyTrashed()->findOrFail($id);

        $ingredient->restore();
    }
}
