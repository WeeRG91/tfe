<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateIngredient
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param Ingredient $ingredient
     * @param array $data
     * @param array $files
     * @return Ingredient
     * @throws Throwable
     */
    public function execute(Ingredient $ingredient, array $data, array $files = []): Ingredient
    {
        return DB::transaction(function () use ($ingredient, $data, $files) {
            $ingredient->update($data);

            if (!empty($files)) {
                $this->imageService->upload($ingredient, $files);
            }

            return $ingredient;
        });
    }
}
