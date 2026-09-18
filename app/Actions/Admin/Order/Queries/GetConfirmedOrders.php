<?php

namespace App\Actions\Admin\Order\Queries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class GetConfirmedOrders
{
    public function execute(): Collection
    {
        return Order::query()
            ->with([
                'user',
                'items.item',
                'items.meat',
                'items.removedIngredients',
                'address',
            ])
            ->whereNotNull('confirmed_at')
            ->get();
    }
}
