<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;

class RestoreAllergen
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $allergen = Allergen::onlyTrashed()->findOrFail($id);

        $allergen->restore();
    }
}
