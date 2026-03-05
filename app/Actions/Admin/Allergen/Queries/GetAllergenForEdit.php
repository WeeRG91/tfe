<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Http\Resources\Admin\Allergen\AllergenEditResource;
use App\Models\Allergen;
use Illuminate\Support\Facades\Storage;

class GetAllergenForEdit
{
    /**
     * @param Allergen $allergen
     * @return AllergenEditResource
     */
    public function execute(Allergen $allergen): AllergenEditResource
    {
        $allergen->load(['ingredients', 'images']);

        return new AllergenEditResource($allergen);
    }
}
