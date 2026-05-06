<?php

namespace App\Actions\Client\Cart\Commands;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class RemoveCartItem
{
    public function __construct(
        protected GetOrCreateCart $getOrCreateCart,
    ) {}

    /**
     * @param Request $request
     * @param int $cartItemId
     * @return string
     */
    public function execute(Request $request, int $cartItemId): string
    {
        $cart = $this->getOrCreateCart->execute($request);

        $cartItem = $cart->items()->findOrFail($cartItemId);

        $cartItem->load('item');

        $itemName = $cartItem->item->name ?? 'Item';

        $cartItem->delete();

        return $itemName;
    }
}
