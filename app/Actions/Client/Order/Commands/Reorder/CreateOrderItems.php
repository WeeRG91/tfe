<?php

namespace App\Actions\Client\Order\Commands\Reorder;

use App\Models\Order;

class CreateOrderItems
{
    /**
     * @param Order $order
     * @param Order $newOrder
     * @return array{0: float, 1: array}
     */
    public function execute(Order $order, Order $newOrder): array
    {
        $itemsTotalIncVat = 0;
        $vatBreakdown = [];

        foreach ($order->items as $item) {
            $vatRate = $item->vat_rate ?? 0;

            $vatAmount = $item->total_inc_vat - ($item->total_inc_vat / (1 + $vatRate / 100));

            if (!isset($vatBreakdown[$vatRate])) {
                $vatBreakdown[$vatRate] = [
                    'vat_rate' => $vatRate,
                    'vat_amount' => 0,
                    'total_inc_vat' => 0,
                ];
            }

            $vatBreakdown[$vatRate]['vat_amount'] += $vatAmount;
            $vatBreakdown[$vatRate]['total_inc_vat'] += $item->total_inc_vat;

            $newOrderItems = $newOrder->items()->create([
                'item_id' => $item->item_id,
                'item_type' => $item->item_type,
                'meat_id' => $item->meat_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_inc_vat' => $item->total_inc_vat,
                'notes' => $item->notes,
            ]);

            if ($item->removedIngredients()->exists()) {
                $newOrderItems->removedIngredients()->attach($item->removedIngredients->pluck('id'));
            }

            $itemsTotalIncVat += $item->total_inc_vat;
        }

        return [
            $itemsTotalIncVat,
            array_values($vatBreakdown),
        ];
    }
}
