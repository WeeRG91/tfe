<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use App\Services\ImageService;

readonly class ForceDeleteMeat
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function execute(int $id): void
    {
        $meat = Meat::onlyTrashed()->findOrFail($id);

        $meat->dishes()->detach();

        if ($meat->images) {
            foreach ($meat->images as $image) {
                $this->imageService->delete($image);
            }
        }

        $meat->forceDelete();
    }
}
