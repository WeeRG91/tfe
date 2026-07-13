<?php

namespace App\Http\Resources\Admin\User;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Http\Resources\Client\LoyaltyPointTransaction\LoyaltyPointTransactionResource;
use App\Http\Resources\Client\Order\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles
                    ->sortBy(['name', 'asc'])
                    ->values()
                    ->map(fn ($role) => new RoleResource($role));
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions
                    ->sortBy([
                        ['category', 'asc'],
                        ['name', 'asc'],
                    ])
                    ->values()
                    ->map(fn ($permission) => new PermissionResource($permission));
            }),
            'loyalty_points' => $this->whenLoaded('loyaltyPointTransactions', function () {
                return $this->loyaltyPointTransactions
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(fn ($loyaltyPointTransaction) => new LoyaltyPointTransactionResource($loyaltyPointTransaction));
            }),
            'orders' => $this->whenLoaded('orders', function () {
                return $this->orders
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(fn ($order) => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => OrderStatusEnum::getStatus($order->status),
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
