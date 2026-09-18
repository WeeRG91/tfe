<?php

namespace App\Actions\Client\Order\Commands;

use App\Models\Order;

class DeleteOrder
{
    public function execute(Order $order): void
    {
        $order->delete();
    }
}
