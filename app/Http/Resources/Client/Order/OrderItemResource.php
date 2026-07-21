<?php

namespace App\Http\Resources\Client\Order;

use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use App\Enums\ItemTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'item_id' => $this->item_id,
            'item_type' => ItemTypeEnum::fromModel($this->item_type),
            'item' => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'price' => $this->item->price,
                'main_image' => $this->item->main_image
                    ? Storage::disk('public')->url($this->item->main_image)
                    : Storage::disk('public')->url('/images/picture.png'),
                'category' => ItemTypeEnum::fromModel($this->item_type) === ItemTypeEnum::DISH
                    ? DishCategoryEnum::getCategory($this->item->category)
                    : DrinkCategoryEnum::getCategory($this->item->category),
            ],
            'meat' => $this->meat
                ? [
                    'id' => $this->meat->id,
                    'name' => $this->meat->name,
                    'extra_price' => $this->meat->extra_price,
                    'main_image' => $this->meat->main_image
                        ? Storage::disk('public')->url($this->meat->main_image)
                        : Storage::disk('public')->url('/images/picture.png'),
                ]
                : null,
            'removed_ingredients' => $this->removedIngredients
                ? $this->removedIngredients->map(fn ($ingredient) => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'main_image' => $ingredient->main_image,
                ]) : [],
            'quantity' => $this->quantity,
            'spicy_level' => $this->spicy_level,
            'unit_price' => $this->unit_price,
            'total_inc_vat' => $this->total_inc_vat,
            'notes' => $this->notes,
        ];
    }
}
