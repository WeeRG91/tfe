<?php

namespace App\Actions\Client\Order\Queries;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;

class GetReorderData
{
    public function execute(User $user, Order $order): array
    {
        $order->load([
            'user',
            'items.item',
            'items.meat',
            'items.removedIngredients',
        ]);

        $addresses = Address::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->get();

        return [
            'order' => $order,
            'addresses' => $addresses,
        ];
    }
}
