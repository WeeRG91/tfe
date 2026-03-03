<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateMeat
{
    public function __construct(
        private ImageService $imageService
    ) {}

    /**
     * @param Meat $meat
     * @param array $data
     * @param array $files
     * @return Meat
     * @throws Throwable
     */
    public function execute(Meat $meat, array $data, array $files = []): Meat
    {
        return DB::transaction(function () use ($meat, $data, $files) {
            $meat->update($data);

            if (!empty($files)) {
                $this->imageService->upload($meat, $files);

                $meat->update([
                    'main_image' => $meat->mainImage->path
                ]);
            }

            return $meat;
        });
    }
}
