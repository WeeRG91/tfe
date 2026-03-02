<?php

namespace App\Actions\Admin\Dish\Commands;

use App\Models\Dish;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateDish
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param array $data
     * @param array $ingredientIds
     * @param array $files
     * @return Dish
     * @throws Throwable
     */
    public function execute(array $data, array $files, array $ingredientIds): Dish
    {
        return DB::transaction(function () use ($data, $files, $ingredientIds) {
            $dish = Dish::query()->create($data);

            $dish->ingredients()->sync($ingredientIds);

            if (!empty($files)) {
                $this->imageService->upload($dish, $files);
            }

            return $dish;
        });
    }
}
