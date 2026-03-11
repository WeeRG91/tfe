<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;
use App\Services\ImageService;

readonly class ForceDeleteAllergen
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
        $allergen = Allergen::onlyTrashed()->findOrFail($id);

        if ($allergen->images) {
            foreach ($allergen->images as $image) {
                $this->imageService->delete($image);
            }
        }

        $allergen->forceDelete();
    }
}
