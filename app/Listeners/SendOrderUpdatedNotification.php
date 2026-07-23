<?php

namespace App\Listeners;

use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Events\StatusOrderUpdated;
use App\Events\StatusOrderUpdatedBroadcast;
use App\Mail\OrderUpdatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendOrderUpdatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(StatusOrderUpdated $event): void
    {
        $order = $event->order;

        switch ($order->status) {
            case OrderStatusEnum::READY:
                $message = "Your order #$order->order_number is ready for " .
                    match ($order->type) {
                        OrderTypeEnum::TAKEAWAY => 'pickup',
                        OrderTypeEnum::DELIVERY => 'delivery',
                        default => 'serving',
                    } . ".";

                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_READY,
                    'title' => 'Order Ready',
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)->queue(new OrderUpdatedMail($order, OrderStatusEnum::READY->label()));
                break;
            case OrderStatusEnum::DELIVERING:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_DELIVERING,
                    'title' => 'Order Delivering',
                    'message' => "Your order #$order->order_number is out for delivery.",
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)->queue(new OrderUpdatedMail($order, OrderStatusEnum::DELIVERING->label()));
                break;
            case OrderStatusEnum::COMPLETED:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_COMPLETED,
                    'title' => 'Order Completed',
                    'message' => "Your order #$order->order_number has been completed.",
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)->queue(new OrderUpdatedMail($order, OrderStatusEnum::COMPLETED->label()));
                break;
            case OrderStatusEnum::CANCELLED:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_CANCELLED,
                    'title' => 'Order Cancelled',
                    'message' => "Your order #$order->order_number has been cancelled.",
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)->queue(new OrderUpdatedMail($order, OrderStatusEnum::CANCELLED->label()));
                break;
        }

        event(new StatusOrderUpdatedBroadcast($order));
    }
}
