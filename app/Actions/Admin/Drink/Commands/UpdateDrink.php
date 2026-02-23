<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateDrink
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param Drink $drink
     * @param array $data
     * @param array $files
     * @return Drink
     * @throws Throwable
     */
    public function execute(Drink $drink, array $data, array $files = []): Drink
    {
        return DB::transaction(function () use ($drink, $data, $files) {
            $drink->update($data);

            if (!empty($files)) {
                $this->imageService->upload($drink, $files);
            }

            return $drink;
        });
    }
}
