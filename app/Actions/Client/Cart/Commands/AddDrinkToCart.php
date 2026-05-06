<?php

namespace App\Actions\Client\Cart\Commands;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class AddDrinkToCart
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

        $existingItem = $cart->items()
            ->where('item_id', $data['item_id'])
            ->where('item_type', $data['item_type'])
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $data['quantity']
            ]);
            $existingItem->load('item', 'meat');

            $this->updatePrice->execute($existingItem);

            return $existingItem;
        }

        $item = $cart->items()->create([
            'item_id' => $data['item_id'],
            'item_type' => $data['item_type'],
            'quantity' => $data['quantity'],
            'notes' => $data['notes'],
        ]);

        $item->load('item', 'meat');

        $this->updatePrice->execute($item);

       return $item;
    }
}
