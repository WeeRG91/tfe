<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use App\Services\ImageService;

readonly class ForceDeleteIngredient
{
    public function __construct(
        private ImageService $imageService
    ) {}

    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $ingredient = Ingredient::onlyTrashed()->findOrFail($id);

        $ingredient->dishes()->detach();

        if ($ingredient->images) {
            foreach ($ingredient->images as $image) {
                $this->imageService->delete($image);
            }
        }

        $ingredient->forceDelete();
    }
}
