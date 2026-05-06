<?php

namespace App\Actions\Client\Cart\Commands;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class AddDishToCart
{
    public function __construct(
        protected GetOrCreateCart $getOrCreateCart,
        protected UpdateCartItemPrice $updatePrice,
    ) {}

    /**
     * @param Request $request
     * @param array $data
     * @return CartItem
     */
    public function execute(Request $request, array $data): CartItem
    {
        $cart = $this->getOrCreateCart->execute($request);

        $itemToAdd = $cart->items()
            ->where('item_id', $data['item_id'])
            ->where('item_type', $data['item_type'])
            ->where('meat_id', $data['meat_id'])
            ->with('removedIngredients')
            ->get();

        /** @var CartItem $existingItem */
        $existingItem = $itemToAdd->first(function ($cartItem) use ($data) {
            $existingRemovedIngredients = $cartItem->removedIngredients->pluck('id')->values();
            $newRemovedIngredients = collect($data['removed_ingredients'] ?? [])->sort()->values();

            return $existingRemovedIngredients->values()->all() === $newRemovedIngredients->values()->all();
        });

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $data['quantity']
            ]);
            $existingItem->load('item', 'meat');

            $this->updatePrice->execute($existingItem);

            return $existingItem;
        }

        /** @var CartItem $item */
        $item = $cart->items()->create([
            'item_id' => $data['item_id'],
            'item_type' => $data['item_type'],
            'meat_id' => $data['meat_id'],
            'quantity' => $data['quantity'],
            'notes' => $data['notes'],
        ]);

        if (!empty($data['removed_ingredients'])) {
            $item->removedIngredients()->sync($data['removed_ingredients']);
        }

        $item->load('item', 'meat');

        $this->updatePrice->execute($item);

        return $item;
    }
}
