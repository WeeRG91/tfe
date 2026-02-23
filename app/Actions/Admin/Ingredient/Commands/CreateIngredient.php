<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateIngredient
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param array $data
     * @param array $files
     * @return Ingredient
     * @throws Throwable
     */
    public function execute(array $data, array $files = []): Ingredient
    {
        return DB::transaction(function () use ($data, $files) {
            $ingredient = Ingredient::create($data);

            if (!empty($files)) {
                $this->imageService->upload($ingredient, $files);
            }

            return $ingredient;
        });
    }
}
