<?php

namespace App\Actions\Client\Cart\Commands;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use Illuminate\Http\Request;

class UpdateCartItemNotes
{
    public function __construct(
        protected GetOrCreateCart $getOrCreateCart,
    ) {}

    /**
     * @param Request $request
     * @param int $cartItemId
     * @param string|null $notes
     * @return void
     */
    public function execute(Request $request, int $cartItemId, ?string $notes): void
    {
        $cart = $this->getOrCreateCart->execute($request);

        $cartItem = $cart->items()->findOrFail($cartItemId);

        $cartItem->update(['notes' => $notes]);
    }
}
