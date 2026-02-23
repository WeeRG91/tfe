<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Models\Drink;
use Illuminate\Support\Facades\Storage;

class GetDrinkForEdit
{
    /**
     * @param Drink $drink
     * @return array
     */
    public function execute(Drink $drink): array
    {
        $drink->load('images');

        return [
            'id' => $drink->id,
            'name' => $drink->name,
            'description' => $drink->description,
            'category' => $drink->category,
            'price' => $drink->price,
            'images' => $drink->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];
    }
}
