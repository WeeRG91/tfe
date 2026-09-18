<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateIngredient
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        array $data,
        array $files = []
    ): Ingredient {
        return DB::transaction(function () use (
            $data,
            $files
        ) {
            $locale = app()->getLocale();

            $ingredientAttributes = Arr::only($data,
                [
                    'allergen_id',
                ]
            );

            $ingredient = Ingredient::query()->create($ingredientAttributes);

            $translation = $ingredient->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $ingredient->save();

            if ($files !== []) {
                $this->imageService->upload($ingredient, $files);

                $ingredient->update([
                    'main_image' => $ingredient->mainImage->path,
                ]);
            }

            return $ingredient->refresh();
        });
    }
}
