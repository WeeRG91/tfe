<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateAllergen
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param array $data
     * @param array $files
     * @return Allergen
     * @throws Throwable
     */
    public function execute(
        array $data,
        array $files
    ): Allergen
    {
        return DB::transaction(function () use (
            $data, $files
        ) {
            $locale = app()->getLocale();

            $allergen = Allergen::query()->create();

            $translation = $allergen->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $allergen->save();

            if ($files !== []) {
                $this->imageService->upload($allergen, $files);

                $allergen->update([
                    'main_image' => $allergen->mainImage->path,
                ]);
            }

            $ingredientIds = $data['ingredients'] ?? [];

            if ($ingredientIds !== []) {
                Ingredient::query()
                    ->whereIn('id', $ingredientIds)
                    ->update([
                        'allergen_id' => $allergen->id,
                    ]);
            }

            return $allergen->refresh();
        });
    }
}
