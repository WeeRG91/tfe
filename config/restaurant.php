<?php

return [
    /*
   |--------------------------------------------------------------------------
   | Restaurant timezone
   |--------------------------------------------------------------------------
   |
   | Opening hours are interpreted in the restaurant's local timezone.
   |
   */
    'timezone' => env(
        'RESTAURANT_TIMEZONE',
        'Europe/Luxembourg',
    ),

    /*
    |--------------------------------------------------------------------------
    | Takeaway settings
    |--------------------------------------------------------------------------
    */
    'pickup' => [
        'slot_interval_minutes' => (int) env(
            'RESTAURANT_PICKUP_SLOTS_INTERVAL',
            15,
        ),

        'minimum_preparation_minutes' => (int) env(
            'RESTAURANT_MINIMUM_PREPARATION_MINUTES',
            30,
        ),

        'availability_days' => (int) env(
            'RESTAURANT_AVAILABILITY_DAYS',
            7,
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery settings
    |--------------------------------------------------------------------------
    */
    'delivery' => [
        'own_address' => [
            'postal_codes' => array_values(array_filter(array_map(
                'trim',
                explode(
                    ',',
                    (string) env(
                        'RESTAURANT_DELIVERY_POSTAL_CODES',
                        '6700',
                    ),
                ),
            ))),

            'fee' => (float) env(
                'RESTAURANT_OWN_ADDRESS_DELIVERY_FEE',
                2.00,
            ),
        ],

        'company' => [
            'enabled' => (bool) env(
                'RESTAURANT_COMPANY_DELIVERY_ENABLED',
                false,
            ),

            'default_minimum_advance_days' => (int) env(
                'RESTAURANT_COMPANY_DELIVERY_ADVANCE_DAYS',
                2,
            ),

            'fee' => 0.00,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ordering policy
    |--------------------------------------------------------------------------
    |
    | false means customers cannot add products or place an order while the
    | restaurant is currently closed.
    |
    */
    'allow_orders_while_closed' => (bool) env(
        'RESTAURANT_ALLOW_ORDERS_WHILE_CLOSED',
        false,
    ),

    'next_open_search_days' => 90,
];
