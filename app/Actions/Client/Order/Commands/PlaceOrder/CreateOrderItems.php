<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Models\Cart;
use App\Models\Order;

class CreateOrderItems
{
    /**
     * @param Order $order
     * @param Cart $cart
     * @return int[]
     */
    public function execute(Order $order, Cart $cart): array
    {
        $subtotal = 0;
        $foodTotal = 0;
        $drinksTotal = 0;

        foreach ($cart->items as $item) {
            $orderItem = $order->items()->create([
                'item_id' => $item->item_id,
                'item_type' => $item->item_type,
                'meat_id' => $item->meat_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'notes' => $item->notes,
            ]);

            if ($item->removedIngredients()->exists()) {
                $orderItem->removedIngredients()->attach(
                    $item->removedIngredients->pluck('id')
                );
            }

            $subtotal += $item->total;

            if ($item->item_type === 'dish') {
                $foodTotal += $item->total;
            } else {
                $drinksTotal += $item->total;
            }
        }

        return [$subtotal, $foodTotal, $drinksTotal];
    }
}
