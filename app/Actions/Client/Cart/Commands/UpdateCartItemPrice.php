<?php

namespace App\Actions\Client\Cart\Commands;

use App\Models\CartItem;

class UpdateCartItemPrice
{
    /**
     * @param CartItem $item
     * @return void
     */
    public function execute(CartItem $item): void
    {
        $basePrice = $item->item->price ?? 0;
        $meatPrice = $item->meat->extra_price ?? 0;

        $unitPrice = $basePrice + $meatPrice;

        $item->update([
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $item->quantity,
        ]);
    }
}
