<?php

namespace App\Actions\Admin\Support;

use App\Models\Allergen;
use App\Models\Dish;
use App\Models\Drink;
use App\Models\Ingredient;
use App\Models\Meat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class GlobalSearch
{
    private array $models = [
        'dish' => Dish::class,
        'drink' => Drink::class,
        'ingredient' => Ingredient::class,
        'meat' => Meat::class,
        'allergen' => Allergen::class,
    ];

    /**
     * @param string $type
     * @param Collection $items
     * @return array
     */
    private function formatItems(string $type, Collection $items): array
    {
        return $items->map(fn ($item) => [
            'type' => $type,
            'id' => $item->id,
            'name' => $item->name,
            'main_image' => $item->main_image
                ? Storage::disk('public')->url($item->main_image)
                : Storage::disk('public')->url('/images/picture.png'),
        ])->toArray();
    }

    /**
     * @param string $query
     * @return array
     */
    public function execute(string $query): array
    {
        $results = [];

        foreach ($this->models as $type => $model) {
            $items = $model::query()
                ->where('name', 'LIKE', "%$query%")
                ->limit(5)
                ->get();

            $results[$type] = $this->formatItems($type, $items);
        }

        return $results;
    }
}
