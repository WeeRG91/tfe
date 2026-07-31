<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use App\Services\ImageService;
use Illuminate\Support\Arr;
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
    public function execute(
        array $data,
        array $files = []
    ): Meat
    {
        return DB::transaction(function () use (
            $data,
            $files
        ) {
            $locale = app()->getLocale();

            $meatAttributes = Arr::only(
                $data,
                [
                    'extra_price',
                ]
            );

            $meat = Meat::query()->create($meatAttributes);

            $translation = $meat->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $meat->save();

            if ($files !== []) {
                $this->imageService->upload($meat, $files);

                $meat->update([
                    'main_image' => $meat->mainImage->path,
                ]);
            }

            return $meat->refresh();
        });
    }
}
