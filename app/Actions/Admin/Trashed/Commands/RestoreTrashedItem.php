<?php

namespace App\Actions\Admin\Trashed\Commands;

use App\Enums\TrashTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestoreTrashedItem
{
    /**
     * @param int $id
     * @param TrashTypeEnum $type
     * @return Model|null
     */
    public function execute(int $id, TrashTypeEnum $type): ?Model
    {
        /** @var class-string<Model&SoftDeletes> $modelClass */
        $modelClass = $type->model();

        /** @var Model&SoftDeletes $item */
        $item = $modelClass::query()->onlyTrashed()->find($id);

        if (!$item) {
            return null;
        }

        $item->restore();

        return $item;
    }
}
