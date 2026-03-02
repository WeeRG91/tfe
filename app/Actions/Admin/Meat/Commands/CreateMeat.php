<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateMeat
{
    public function __construct(
        private ImageService $imageService,
    ) {}


    /**
     * @param array $data
     * @param array $files
     * @return Meat
     * @throws Throwable
     */
    public function execute(array $data, array $files = []): Meat
    {
        return DB::transaction(function () use ($data, $files) {
            $meat = Meat::query()->create($data);

            if (!empty($files)) {
                $this->imageService->upload($meat, $files);
            }

            $meat->load('images');
            if ($meat->main_image && $meat->images->isNotEmpty()) {
                $meat->update([
                    'main_image' => $meat->images->first()->path
                ]);
            }

            return $meat;
        });
    }
}
