<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;

class DeleteAllergen
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
       $allergen = Allergen::findOrFail($id);

       $allergen->delete();
    }
}
