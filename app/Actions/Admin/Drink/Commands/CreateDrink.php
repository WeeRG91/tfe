<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateDrink
{
    public function __construct(
        private ImageService $imageService
    ) {}

    /**
     * @param array $data
     * @param array $files
     * @return Drink
     * @throws Throwable
     */
    public function execute(array $data, array $files = []): Drink
    {
        return DB::transaction(function () use ($data, $files) {
            $drink = Drink::create($data);

            if (!empty($files)) {
                $this->imageService->upload($drink, $files);

                $drink->update([
                    'main_image' => $drink->mainImage->path,
                ]);
            }

            return $drink;
        });
    }
}
