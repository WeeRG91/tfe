<?php

namespace App\Actions\Admin\Ingredient\Commands;

use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateIngredient
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        Ingredient $ingredient,
        array $data,
        array $files = []
    ): Ingredient {
        return DB::transaction(function () use (
            $ingredient,
            $data,
            $files
        ) {
            $locale = app()->getLocale();

            $ingredient->fill(Arr::only($data,
                [
                    'allergen_id',
                ])
            );

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
