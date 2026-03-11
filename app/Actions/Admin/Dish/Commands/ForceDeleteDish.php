<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use App\Services\ImageService;

readonly class ForceDeleteDish
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $dish = Dish::onlyTrashed()->findOrFail($id);

        $dish->ingredients()->detach();
        $dish->meats()->detach();

        if ($dish->images) {
            foreach ($dish->images as $image) {
                $this->imageService->delete($image);
            }
        }

        $dish->forceDelete();
    }
}
