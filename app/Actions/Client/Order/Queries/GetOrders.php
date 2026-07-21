<?php

namespace App\Actions\Client\Order\Queries;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetOrders
{
    /**
     * @param User $user
     * @param string|null $status
     * @return Collection
     */
    public function execute(User $user, ?string $status = null): Collection
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->when($status, function ($query) use ($status) {
                match ($status) {
                    'active' => $query->whereIn(
                        'status',
                        OrderStatusEnum::activeStatuses()
                    ),

                    'completed' => $query->where(
                        'status',
                        OrderStatusEnum::COMPLETED->value
                    ),

                    'cancelled' => $query->where(
                        'status',
                        OrderStatusEnum::CANCELLED->value
                    ),

                    default => null,
                };
            })
            ->with([
                'user',
                'items.item',
                'items.meat',
                'items.removedIngredients',
                'address',
            ])
            ->latest()
            ->get();
    }
}
