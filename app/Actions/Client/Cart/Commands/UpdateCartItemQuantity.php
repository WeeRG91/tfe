<?php

namespace App\Actions\Client\Cart\Commands;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class UpdateCartItemQuantity
{
    public function __construct(
        protected GetOrCreateCart $getOrCreateCart,
        protected UpdateCartItemPrice $updatePrice,
    ) {}

    /**
     * @param Request $request
     * @param int $cartItemId
     * @param string $action
     * @return string
     */
    public function execute(Request $request, int $cartItemId, string $action): string
    {
        $cart = $this->getOrCreateCart->execute($request);

        /** @var CartItem $cartItem */
        $cartItem = $cart->items()->findOrFail($cartItemId);

        if ($action === 'increase') {
            $cartItem->update([
                'quantity' => $cartItem->quantity + 1
            ]);
        } else {
            if ($cartItem->quantity <= 1) {
                $cartItem->delete();

                return 'deleted';
            }

            $cartItem->update([
                'quantity' => $cartItem->quantity - 1
            ]);
        }

        $cartItem->load('item', 'meat');

        $this->updatePrice->execute($cartItem);

        return 'updated';
    }
}
