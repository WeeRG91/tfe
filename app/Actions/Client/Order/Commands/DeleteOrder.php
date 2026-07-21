<?php

namespace App\Actions\Client\Order\Commands;

use App\Models\Order;

class DeleteOrder
{
    /**
     * @param Order $order
     * @return void
     */
    public function execute(Order $order): void
    {
        $order->delete();
    }
}
