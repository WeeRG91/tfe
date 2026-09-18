<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateMeat
{
    public function __construct(
        private ImageService $imageService
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        Meat $meat,
        array $data,
        array $files = []
    ): Meat {
        return DB::transaction(function () use (
            $meat,
            $data,
            $files
        ) {
            $locale = app()->getLocale();

            $meat->fill(Arr::only($data,
                [
                    'extra_price',
                ])
            );

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
