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
    public function execute(array $data, array $files = []): Allergen
    {
        return DB::transaction(function () use ($data, $files) {
            $allergen = Allergen::create($data);

            if (!empty($files)) {
                $this->imageService->upload($allergen, $files);

                $allergen->update([
                    'main_image' => $allergen->mainImage->path,
                ]);
            }

            if (!empty($data['ingredients'])) {
                Ingredient::whereIn('id', $data['ingredients'])
                    ->update(['allergen_id' => $allergen->id]);
            }

            return $allergen;
        });
    }
}
