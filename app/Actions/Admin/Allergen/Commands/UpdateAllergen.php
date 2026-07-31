<?php

namespace App\Actions\Admin\Allergen\Commands;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateAllergen
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param Allergen $allergen
     * @param array $data
     * @param array $files
     * @return Allergen
     * @throws Throwable
     */
    public function execute(
        Allergen $allergen,
        array $data,
        array $files = []
    ): Allergen
    {
        return DB::transaction(function () use (
            $allergen,
            $data,
            $files
        ) {
            $locale = app()->getLocale();

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

            Ingredient::query()
                ->where('allergen_id', $allergen->id)
                ->update(['allergen_id' => null,]);

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
