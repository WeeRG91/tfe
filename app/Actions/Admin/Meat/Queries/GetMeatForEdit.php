<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Models\Meat;
use Illuminate\Support\Facades\Storage;

class GetMeatForEdit
{
    /**
     * @param Meat $meat
     * @return array
     */
    public function execute(Meat $meat): array
    {
        $meat->load('images');

        return [
            'id' => $meat->id,
            'name' => $meat->name,
            'description' => $meat->description,
            'extra_price' => $meat->extra_price,
            'images' => $meat->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];
    }
}
