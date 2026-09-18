<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;

class DeleteAllergen
{
    public function execute(int $id): void
    {
        $allergen = Allergen::findOrFail($id);

        $allergen->delete();
    }
}
