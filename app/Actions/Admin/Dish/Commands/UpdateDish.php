<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateDish
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param Dish $dish
     * @param array $data
     * @param array $files
     * @param array $ingredientIds
     * @return Dish
     * @throws Throwable
     */
    public function execute(Dish $dish, array $data, array $files, array $ingredientIds): Dish
    {
        return DB::transaction(function () use ($dish, $data, $files,$ingredientIds) {
            $dish->update($data);

            $dish->ingredients()->sync($ingredientIds);

            if (!empty($files)) {
                $this->imageService->upload($dish, $files);
            }

            return $dish;
        });
    }
}
