<?php

namespace App\Actions\Admin\Trashed\Queries;

use App\Enums\TrashTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GetAllTrashedItems
{
    /**
     * @return Collection
     */
    public function execute(): Collection
    {
        return collect(TrashTypeEnum::cases())
            ->flatMap(fn ($type) =>
                $this->getTrashedItems(
                    $type->model(),
                    $type->value
                )
            )
            ->sortByDesc('deleted_at')
            ->values();
    }

    /**
     * @param string $model
     * @param string $type
     * @return array
     */
    public function getTrashedItems(string $model, string $type): array
    {
        /** @var Model $model */
        return $model::query()
            ->onlyTrashed()
            ->with('images')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'image' => $item->images->first()
                    ? Storage::disk('public')->url($item->images->first()->path)
                    : Storage::disk('public')->url("images/picture.png"),
                'type' => $type,
                'deleted_at' => $item->deleted_at,
            ])
            ->toArray();
    }
}
