<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;

class DeleteIngredient
{
    public function execute(int $id): void
    {
        $ingredient = Ingredient::findOrFail($id);

        $ingredient->delete();
    }
}
