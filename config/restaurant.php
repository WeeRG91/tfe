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
];
