<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteAllergen
{
    /**
     * @param Allergen $allergen
     * @return void
     * @throws Throwable
     */
    public function execute(Allergen $allergen): void
    {
        DB::transaction(function () use ($allergen) {
            Ingredient::where('allergen_id', $allergen->id)
                ->update(['allergen_id' => null]);

            $allergen->delete();
        });
    }
}
