<?php

namespace App\Http\Resources\Client;

use App\Enums\DishCategoryEnum;
use App\Enums\DrinkCategoryEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartResource extends JsonResource
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
            'guest_token' => $this->guest_token,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'items' => $this->whenLoaded('items')->map(fn ($item) => [
                'id' => $item->id,
                'cart_id' => $item->cart_id,
                'item_id' => $item->item_id,
                'item_type' => $item->item_type,
                'item' => [
                    'id' => $item->item->id,
                    'name' => $item->item->name,
                    'price' => $item->item->price,
                    'main_image' => $item->item->main_image
                        ? Storage::disk('public')->url($item->item->main_image)
                        : Storage::disk('public')->url('/images/picture.png'),
                    'category' => $item->item_type === 'dish'
                        ? DishCategoryEnum::getCategory($item->item->category)
                        : DrinkCategoryEnum::getCategory($item->item->category),
                ],
                'meat' => [
                    'id' => $item->meat->id,
                    'name' => $item->meat->name,
                    'extra_price' => $item->meat->extra_price,
                    'main_image' => $item->meat->main_image
                        ? Storage::disk('public')->url($item->meat->main_image)
                        : Storage::disk('public')->url('/images/picture.png'),
                ],
                'removed_ingredients' => $item->removedIngredients->map(fn ($ingredient) => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'main_image' => $ingredient->main_image,
                ]),
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'notes' => $item->notes,
            ])
        ];
    }
}
