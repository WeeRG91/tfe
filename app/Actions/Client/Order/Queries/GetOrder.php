<?php

namespace App\Actions\Client\Order\Queries;

use App\Models\Order;

class GetOrder
{
    public function execute(Order $order): Order
    {
        return $order->load([
            'user',
            'items.item',
            'items.meat',
            'items.removedIngredients',
            'address',
        ]);
    }
}
