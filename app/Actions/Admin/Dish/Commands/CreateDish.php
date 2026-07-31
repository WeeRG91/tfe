<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateDish
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param array $data
     * @param array $files
     * @param array $meatIds
     * @param array $ingredientIds
     * @return Dish
     * @throws Throwable
     */
    public function execute(
        array $data,
        array $files,
        array $meatIds,
        array $ingredientIds
    ): Dish
    {
        return DB::transaction(function () use (
            $data,
            $files,
            $meatIds,
            $ingredientIds
        ) {
            $locale = app()->getLocale();

            $dishAttributes = Arr::only($data, [
                'category',
                'default_spicy_level',
                'price',
            ]);

            $dish = Dish::query()->create(
                $dishAttributes
            );

            $translation =
                $dish->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],

                'description' =>
                    $data['description'] ?? null,
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
                    'main_image' =>
                        $dish->mainImage->path,
                ]);
            }

            return $dish->refresh();
        });
    }
}
