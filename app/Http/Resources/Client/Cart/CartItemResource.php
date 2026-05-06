<?php

namespace App\Http\Resources\Client\Cart;

use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'item_id' => $this->item_id,
            'item_type' => $this->item_type,
            'item' => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'price' => $this->item->price,
                'main_image' => $this->item->main_image
                    ? Storage::disk('public')->url($this->item->main_image)
                    : Storage::disk('public')->url('/images/picture.png'),
                'category' => $this->item_type === 'dish'
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
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'notes' => $this->notes,
        ];
    }
}
