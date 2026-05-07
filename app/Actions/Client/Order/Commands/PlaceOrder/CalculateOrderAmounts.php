<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

class CalculateOrderAmounts
{
    /**
     * @param float $subtotal
     * @param float $foodTotal
     * @param float $drinksTotal
     * @param int $usedPoints
     * @param int $type
     * @return array
     */
    public function execute(
        float $subtotal,
        float $foodTotal,
        float $drinksTotal,
        int $usedPoints,
        int $type
    ): array
    {
        $rewards = [
            300 => 5,
            550 => 10,
        ];

        $discountTotal = $rewards[$usedPoints] ?? 0;

        $deliveryFee = $type === 3 ? 2 : 0;

        $grossTotal = $subtotal + $deliveryFee;

        $finalTotal = max($grossTotal - $discountTotal, 0);

        $totalBase = $subtotal > 0 ? $subtotal : 1;

        $foodRatio = $foodTotal / $totalBase;
        $drinksRatio = $drinksTotal / $totalBase;

        $finalFoodIncVat = $finalTotal * $foodRatio;
        $finalDrinksIncVat = $finalTotal * $drinksRatio;

        $vatFoodRate = 12;
        $vatDrinksRate = 21;

        $foodNet = $finalFoodIncVat / (1 + ($vatFoodRate / 100));
        $foodVat = $finalFoodIncVat - $foodNet;

        $drinksNet = $finalDrinksIncVat / (1 + ($vatDrinksRate / 100));
        $drinksVat = $finalDrinksIncVat - $drinksNet;

        $vatTotal = $foodVat + $drinksVat;
        $netTotal = $foodNet + $drinksNet;

        return [
            'subtotal' => $subtotal,
            'discount_rate' => 0,
            'discount_total' => $discountTotal,
            'vat_food_rate' => $vatFoodRate,
            'vat_food_amount' => round($foodVat, 2),
            'final_food_inc_vat' => round($finalFoodIncVat, 2),
            'vat_drinks_rate' => $vatDrinksRate,
            'vat_drinks_amount' => round($drinksVat, 2),
            'final_drinks_inc_vat' => round($finalDrinksIncVat, 2),
            'vat_total' => round($vatTotal, 2),
            'delivery_fee' => $deliveryFee,
            'net_total' => $netTotal,
            'total' => round($finalTotal, 2),
        ];
    }
}
