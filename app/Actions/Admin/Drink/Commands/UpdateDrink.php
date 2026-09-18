<?php

namespace App\Actions\Admin\Drink\Commands;

use App\Models\Drink;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateDrink
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        Drink $drink,
        array $data,
        array $files = []
    ): Drink {
        return DB::transaction(function () use (
            $drink,
            $data,
            $files
        ) {
            $locale = app()->getLocale();

            $drink->fill(Arr::only($data,
                [
                    'category',
                    'price',
                ])
            );

            $translation = $drink->translateOrNew($locale);

            $translation->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $drink->save();

            if ($files !== []) {
                $this->imageService->upload($drink, $files);

                $drink->update(['main_image' => $drink->mainImage->path]);
            }

            return $drink->refresh();
        });
    }
}
