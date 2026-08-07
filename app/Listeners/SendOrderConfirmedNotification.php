<?php

namespace App\Listeners;

use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Events\StatusOrderUpdatedBroadcast;
use App\Events\OrderPlacedBroadcast;
use App\Mail\OrderUpdatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderPlacedBroadcast $event): void
    {
        $order = $event->order;

        DB::table('notifications')->insert([
            'user_id' => $order->user_id,
            'notifiable_id' => $order->id,
            'notifiable_type' => Order::class,
            'type' => NotificationTypeEnum::ORDER_CONFIRMED,
            'title' => 'order_confirmed',
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
                OrderStatusEnum::CONFIRMED,
            ));

        event(new StatusOrderUpdatedBroadcast($order));
    }
}
