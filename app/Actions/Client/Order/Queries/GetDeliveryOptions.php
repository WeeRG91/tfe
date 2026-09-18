<?php

namespace App\Actions\Client\Order\Queries;

use App\Models\DeliveryCompany;
use Carbon\CarbonImmutable;

class GetDeliveryOptions
{
    public function execute(): array
    {
        $companyDeliveryEnabled = (bool) config(
            'restaurant.delivery.company.enabled',
            false,
        );

        $companies = collect();

        if ($companyDeliveryEnabled) {
            $today = CarbonImmutable::now(
                config('restaurant.timezone'),
            )->startOfDay();

            $companies = DeliveryCompany::query()
                ->where('is_active', true)
                ->with([
                    'deliveryDates' => fn ($query) => $query
                        ->where('is_available', true)
                        ->orderBy('delivery_date'),
                ])
                ->orderBy('name')
                ->get()
                ->map(function (
                    DeliveryCompany $company,
                ) use ($today): array {
                    $earliestDate = $today->addDays(
                        $company->minimum_advance_days,
                    );

                    $dates = $company->deliveryDates
                        ->filter(
                            fn ($date) => $date
                                ->delivery_date
                                ->startOfDay()
                                ->greaterThanOrEqualTo($earliestDate)
                        )
                        ->map(
                            fn ($date) => $date
                                ->delivery_date
                                ->format('Y-m-d'),
                        )
                        ->values()
                        ->all();

                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'minimum_advance_days' => $company->minimum_advance_days,
                        'dates' => $dates,
                    ];
                })
                ->values();
        }

        return [
            'own_address' => [
                'fee' => number_format(
                    (float) config(
                        'restaurant.delivery.own_address.fee',
                        2.00,
                    ),
                    2,
                    '.',
                    '',
                ),
                'postal_codes' => array_values(
                    (array) config(
                        'restaurant.delivery.own_address.postal_codes',
                        ['6700'],
                    ),
                ),
            ],
            'company' => [
                'enabled' => $companyDeliveryEnabled,
                'fee' => '0.00',
                'companies' => $companies,
            ],
        ];
    }
}
