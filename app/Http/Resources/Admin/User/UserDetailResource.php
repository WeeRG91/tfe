<?php

namespace App\Http\Resources\Admin\User;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Http\Resources\Client\LoyaltyPointTransaction\LoyaltyPointTransactionResource;
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
            'roles' => RoleResource::collection(
                $this->whenLoaded('roles')
            ),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'loyalty_points' => LoyaltyPointTransactionResource::collection(
                $this->whenLoaded('loyaltyPointTransactions')
            ),
            'orders' => $this->whenLoaded('orders')->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->name,
                'status' => OrderStatusEnum::getStatus($order->status),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
