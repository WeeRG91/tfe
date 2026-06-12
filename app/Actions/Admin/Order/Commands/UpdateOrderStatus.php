<?php

namespace App\Actions\Admin\Order\Commands;

use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\StatusOrderUpdated;
use App\Events\StatusOrderUpdatedBroadcast;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatus
{
    /**
     * @param Order $order
     * @param int $newStatus
     * @return void
     */
    public function execute(Order $order, int $newStatus): void
    {
        $currentStatus = $order->status->value;

        $data = [
            'status' => $newStatus,
        ];

        $isBackward = $this->isBackward($currentStatus, $newStatus);

        if (!$isBackward) {
            $data = array_merge($data, $this->forwardTransitions($order, $newStatus));
        } else {
            $data = array_merge($data, $this->backwardTransitions($order, $newStatus));
        }

        $order->update($data);

        $order->refresh();

        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        if ($isBackward) {
            $this->removeNotification($order, $currentStatus);
            event(new StatusOrderUpdated($order));
        } else {
            event(new StatusOrderUpdated($order));
        }
    }

    /**
     * @param int $currentStatus
     * @param int $newString
     * @return bool
     */
    private function isBackward(int $currentStatus, int $newString): bool
    {
        return match ($currentStatus) {
            OrderStatusEnum::PREPARING->value => $newString === OrderStatusEnum::CONFIRMED->value,
            OrderStatusEnum::READY->value => $newString === OrderStatusEnum::PREPARING->value,
            OrderStatusEnum::DELIVERING->value => $newString === OrderStatusEnum::READY->value,
            OrderStatusEnum::COMPLETED->value => $newString === OrderStatusEnum::DELIVERING->value,
            default => false,
        };
    }

    /**
     * @param Order $order
     * @param int $newStatus
     * @return array
     */
    private function forwardTransitions(Order $order, int $newStatus): array
    {
        $data = [];

        switch ($newStatus) {
            case OrderStatusEnum::PREPARING->value:
                $data['prepare_at'] = now();
                break;
            case OrderStatusEnum::READY->value:
                $data['ready_at'] = now();
                break;
            case OrderStatusEnum::DELIVERING->value:
                $data['delivered_at'] = now();
                break;
            case OrderStatusEnum::COMPLETED->value:
                $data['completed_at'] = now();

                if ($order->payment_method === PaymentMethodEnum::CASH) {
                    $data['paid_at'] = now();
                    $data['payment_status'] = PaymentStatusEnum::PAID->value;
                }
                break;
        }

        return $data;
    }

    /**
     * @param Order $order
     * @param int $newStatus
     * @return array
     */
    private function backwardTransitions(Order $order, int $newStatus): array
    {
        $data = [];

        switch ($newStatus) {
            case OrderStatusEnum::CONFIRMED->value:
                $data['prepare_at'] = null;
                $data['ready_at'] = null;
                $data['delivered_at'] = null;
                $data['completed_at'] = null;
                break;
            case OrderStatusEnum::PREPARING->value:
                $data['ready_at'] = null;
                $data['delivered_at'] = null;
                $data['completed_at'] = null;
                break;
            case OrderStatusEnum::READY->value:
                $data['delivered_at'] = null;
                $data['completed_at'] = null;
                break;
            case OrderStatusEnum::DELIVERING->value:
                $data['completed_at'] = null;
                break;
        }

        if ($order->payment_method === PaymentMethodEnum::CASH) {
            $data['paid_at'] = null;
            $data['payment_status'] = PaymentStatusEnum::PENDING->value;
        }

        return $data;
    }

    private function removeNotification(Order $order, int $previousStatus): void
    {
        $type = match ($previousStatus) {
            OrderStatusEnum::READY->value => NotificationTypeEnum::ORDER_READY->value,
            OrderStatusEnum::DELIVERING->value => NotificationTypeEnum::ORDER_DELIVERING->value,
            OrderStatusEnum::COMPLETED->value => NotificationTypeEnum::ORDER_COMPLETED->value,
            default => null,
        };

        if (!$type) return;

        DB::table('notifications')
            ->where('user_id', $order->user_id)
            ->where('notifiable_id', $order->id)
            ->where('type', $type)
            ->delete();

        event(new StatusOrderUpdatedBroadcast($order));
    }
}
