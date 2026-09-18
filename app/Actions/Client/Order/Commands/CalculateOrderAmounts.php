<?php

namespace App\Actions\Client\Order\Commands;

use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderTypeEnum;

class CalculateOrderAmounts
{
    /**
     * @param float $itemsTotalIncVat
     * @param array $vatBreakdown
     * @param int $usedPoints
     * @param int $type
     * @return array
     */
    public function execute(
        float $itemsTotalIncVat,
        array $vatBreakdown,
        int $usedPoints,
        int $type,
        ?string $deliveryType,
    ): array
    {
        $rewards = [
            300 => 5,
            550 => 10,
        ];

        $discountTotal = $rewards[$usedPoints] ?? 0;

        $isOwnAddressDelivery =
            $type === OrderTypeEnum::DELIVERY->value &&
            $deliveryType ===
            DeliveryTypeEnum::OWN_ADDRESS->value;

        $deliveryFee = $isOwnAddressDelivery
            ? (float) config(
                'restaurant.delivery.own_address.fee',
                2.00,
            )
            : 0.0;

        $discountedItemsTotal = max($itemsTotalIncVat - $discountTotal, 0);

        $adjustedBreakdown = [];

        foreach ($vatBreakdown as $row) {

            $rowRatio = $itemsTotalIncVat > 0
                ? ($row['total_inc_vat'] / $itemsTotalIncVat)
                : 0;

            $rowDiscount = $discountTotal * $rowRatio;

            $newTotalInc = $row['total_inc_vat'] - $rowDiscount;

            $rate = $row['vat_rate'];

            $newVat = $newTotalInc - ($newTotalInc / (1 + $rate / 100));

            $adjustedBreakdown[$rate] = [
                'vat_rate' => $rate,
                'total_inc_vat' => round($newTotalInc, 2),
                'vat_total' => round($newVat, 2),
            ];
        }

        if ($deliveryFee > 0) {
            $deliveryVat = $deliveryFee - ($deliveryFee / 1.21);

            if (isset($adjustedBreakdown[21])) {
                $adjustedBreakdown[21]['total_inc_vat'] += round($deliveryFee, 2);
                $adjustedBreakdown[21]['vat_total'] += round($deliveryVat, 2);
            } else {
                $adjustedBreakdown[21] = [
                    'vat_rate' => 21,
                    'total_inc_vat' => round($deliveryFee, 2),
                    'vat_total' => round($deliveryVat, 2),
                ];
            }
        }

        $finalVatTotal = array_sum(array_column($adjustedBreakdown, 'vat_total'));

        $finalTotal = $discountedItemsTotal + $deliveryFee;

        return [
            'discount_total' => $discountTotal,
            'vat_total' => round($finalVatTotal, 2),
            'vat_breakdown' => array_values($adjustedBreakdown),
            'delivery_fee' => $deliveryFee,
            'subtotal' => $itemsTotalIncVat,
            'total_inc_vat' => round($finalTotal, 2),
        ];
    }
}
