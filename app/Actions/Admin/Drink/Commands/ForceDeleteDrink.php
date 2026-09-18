<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;
use App\Services\ImageService;

readonly class ForceDeleteDrink
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function execute(int $id): void
    {
        $drink = Drink::onlyTrashed()->findOrFail($id);

        if ($drink->images) {
            foreach ($drink->images as $image) {
                $this->imageService->delete($image);
            }
        }

        $drink->forceDelete();
    }
}
