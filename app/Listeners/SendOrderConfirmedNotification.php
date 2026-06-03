<?php

namespace App\Listeners;

use App\Enums\NotificationTypeEnum;
use App\Events\OrderConfirmedBroadcast;
use App\Events\OrderPlacedBroadcast;
use App\Mail\OrderConfirmedMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {}

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
            'title' => 'Order Confirmed',
            'message' => "Your order #{$order->order_number} has been confirmed.",
            'data' => json_encode([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        event(new OrderConfirmedBroadcast($order));

        Mail::to($order->user->email)->queue(new OrderConfirmedMail($order));
    }
}
