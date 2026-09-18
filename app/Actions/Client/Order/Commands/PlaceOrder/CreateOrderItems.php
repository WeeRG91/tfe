<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Enums\DrinkCategoryEnum;
use App\Enums\ItemTypeEnum;
use App\Models\Cart;
use App\Models\Order;

class CreateOrderItems
{
    /**
     * @return array{0: float, 1: array}
     */
    public function execute(Order $order, Cart $cart): array
    {
        $itemsTotalIncVat = 0;
        $vatBreakdown = [];

        foreach ($cart->items as $item) {
            $vatRate = match (ItemTypeEnum::fromModel($item->item_type)) {
                ItemTypeEnum::DISH => 12,

                ItemTypeEnum::DRINK => match ($item->item?->category) {
                    DrinkCategoryEnum::BEER,
                    DrinkCategoryEnum::WINE,
                    DrinkCategoryEnum::COCKTAIL => 21,

                    default => 12,
                },
            };

            $vatAmount = $item->total - ($item->total / (1 + $vatRate / 100));

            if (! isset($vatBreakdown[$vatRate])) {
                $vatBreakdown[$vatRate] = [
                    'vat_rate' => $vatRate,
                    'vat_total' => 0,
                    'total_inc_vat' => 0,
                ];
            }

            $vatBreakdown[$vatRate]['vat_total'] += $vatAmount;
            $vatBreakdown[$vatRate]['total_inc_vat'] += $item->total;

            $orderItems = $order->items()->create([
                'item_id' => $item->item_id,
                'item_type' => $item->item_type,
                'meat_id' => $item->meat_id,
                'quantity' => $item->quantity,
                'spicy_level' => $item->spicy_level,
                'unit_price' => $item->unit_price,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total_inc_vat' => $item->total,
                'notes' => $item->notes,
            ]);

            if ($item->removedIngredients()->exists()) {
                $orderItems->removedIngredients()->attach($item->removedIngredients->pluck('id'));
            }

            $itemsTotalIncVat += $item->total;
        }

        return [
            $itemsTotalIncVat,
            array_values($vatBreakdown),
        ];
    }
}
