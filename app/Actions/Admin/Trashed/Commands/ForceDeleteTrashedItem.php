<?php

namespace App\Actions\Admin\Trashed\Commands;

use App\Enums\TrashTypeEnum;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ForceDeleteTrashedItem
{
    public function __construct(
        private ImageService $imageService,
    ) {}

    /**
     * @param int $id
     * @param TrashTypeEnum $type
     * @return Model|null
     * @throws Throwable
     */
    public function execute(int $id, TrashTypeEnum $type): ?Model
    {
        return DB::transaction(function () use ($id, $type) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $type->model();

            /** @var Model $item */
            $item = $modelClass::query()->onlyTrashed()->find($id);

            if (!$item) {
                return null;
            }

            if ($item instanceof Dish) {
                $item->ingredients()->detach();
            } elseif ($item instanceof Ingredient) {
                $item->dish()->detach();
            }

            if (method_exists($item, 'images')) {
                foreach ($item->images as $image) {
                    $this->imageService->delete($image);
                }
            }

            $item->forceDelete();

            return $item;
        });
    }
}
