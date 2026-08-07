<?php

namespace App\Listeners;

use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
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
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_READY,
                    'title' => 'order_ready',
                    'message' => '',
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_type' => $order->type->key(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)
                    ->locale($order->user->preferredLocale())
                    ->queue(new OrderUpdatedMail(
                        $order,
                        OrderStatusEnum::READY,
                    ));
                break;
            case OrderStatusEnum::DELIVERING:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_DELIVERING,
                    'title' => 'order_delivering',
                    'message' => '',
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_type' => $order->type->key(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)
                    ->locale($order->user->preferredLocale())
                    ->queue(new OrderUpdatedMail(
                        $order,
                        OrderStatusEnum::DELIVERING,
                    ));
                break;
            case OrderStatusEnum::COMPLETED:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_COMPLETED,
                    'title' => 'order_completed',
                    'message' => '',
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_type' => $order->type->key(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)
                    ->locale($order->user->preferredLocale())
                    ->queue(new OrderUpdatedMail(
                        $order,
                        OrderStatusEnum::COMPLETED,
                    ));
                break;
            case OrderStatusEnum::CANCELLED:
                DB::table('notifications')->insert([
                    'user_id' => $order->user_id,
                    'notifiable_id' => $order->id,
                    'notifiable_type' => Order::class,
                    'type' => NotificationTypeEnum::ORDER_CANCELLED,
                    'title' => 'order_cancelled',
                    'message' => '',
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_type' => $order->type->key(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::to($order->user->email)
                    ->locale($order->user->preferredLocale())
                    ->queue(new OrderUpdatedMail(
                        $order,
                        OrderStatusEnum::CANCELLED,
                    ));
                break;
        }

        event(new StatusOrderUpdatedBroadcast($order));
    }
}
