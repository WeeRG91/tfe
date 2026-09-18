<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateDish
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        Dish $dish,
        array $data,
        array $files,
        array $meatIds,
        array $ingredientIds
    ): Dish {
        return DB::transaction(function () use (
            $dish,
            $data,
            $files,
            $meatIds,
            $ingredientIds
        ) {
            $locale = app()->getLocale();

            $dish->fill(
                Arr::only($data, [
                    'category',
                    'default_spicy_level',
                    'price',
                ])
            );

            $translation =
                $dish->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],

                'description' => $data['description'] ?? null,
            ]);

            $dish->save();

            $dish->meats()->sync($meatIds);

            $dish->ingredients()->sync(
                $ingredientIds
            );

            if ($files !== []) {
                $this->imageService->upload(
                    $dish,
                    $files
                );

                $dish->update([
                    'main_image' => $dish->mainImage->path,
                ]);
            }

            return $dish->refresh();
        });
    }
}
