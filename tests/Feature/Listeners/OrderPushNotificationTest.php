<?php

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderPlacedBroadcast;
use App\Events\StatusOrderUpdated;
use App\Events\StatusOrderUpdatedBroadcast;
use App\Jobs\SendExpoPushNotification;
use App\Listeners\SendOrderConfirmedNotification;
use App\Listeners\SendOrderUpdatedNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function orderForPushNotification(
    User $user,
    OrderStatusEnum $status,
): Order {
    return Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => $status,
        'payment_method' => PaymentMethodEnum::CASH,
        'payment_status' => PaymentStatusEnum::PENDING,
        'subtotal' => '12.00',
        'discount_total' => '0.00',
        'vat_total' => '1.29',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '12.00',
    ]);
}

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    Event::fake([StatusOrderUpdatedBroadcast::class]);
});

it('queues a push notification when an order is confirmed', function () {
    $user = User::factory()->withoutTwoFactor()->create(['locale' => 'en']);
    $order = orderForPushNotification($user, OrderStatusEnum::CONFIRMED);

    (new SendOrderConfirmedNotification)->handle(
        new OrderPlacedBroadcast($order),
    );

    Queue::assertPushed(
        SendExpoPushNotification::class,
        fn (SendExpoPushNotification $job): bool => $job->userId === $user->id
            && $job->title === 'Order confirmed'
            && $job->body === "Order #{$order->order_number} has been confirmed."
            && $job->data === [
                'type' => 'order_confirmed',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
    );
});

it('queues a push notification for a mobile order status update', function (
    OrderStatusEnum $status,
    string $type,
    string $title,
    string $body,
) {
    $user = User::factory()->withoutTwoFactor()->create(['locale' => 'en']);
    $order = orderForPushNotification($user, $status);

    (new SendOrderUpdatedNotification)->handle(
        new StatusOrderUpdated($order),
    );

    Queue::assertPushed(
        SendExpoPushNotification::class,
        fn (SendExpoPushNotification $job): bool => $job->userId === $user->id
            && $job->title === $title
            && $job->body === str_replace(':number', $order->order_number, $body)
            && $job->data === [
                'type' => $type,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
    );
})->with([
    'ready' => [
        OrderStatusEnum::READY,
        'order_ready',
        'Order ready',
        'Order #:number is ready.',
    ],
    'delivering' => [
        OrderStatusEnum::DELIVERING,
        'order_delivering',
        'Order out for delivery',
        'Order #:number is out for delivery.',
    ],
    'completed' => [
        OrderStatusEnum::COMPLETED,
        'order_completed',
        'Order completed',
        'Order #:number has been completed.',
    ],
    'cancelled' => [
        OrderStatusEnum::CANCELLED,
        'order_cancelled',
        'Order cancelled',
        'Order #:number has been cancelled.',
    ],
]);

it('uses the customers preferred language for the push message', function () {
    $user = User::factory()->withoutTwoFactor()->create(['locale' => 'fr']);
    $order = orderForPushNotification($user, OrderStatusEnum::READY);

    (new SendOrderUpdatedNotification)->handle(
        new StatusOrderUpdated($order),
    );

    Queue::assertPushed(
        SendExpoPushNotification::class,
        fn (SendExpoPushNotification $job): bool => $job->title === 'Commande prête'
            && $job->body === "La commande n°{$order->order_number} est prête.",
    );
});

it('does not queue a push notification for an unrelated order status', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $order = orderForPushNotification($user, OrderStatusEnum::PREPARING);

    (new SendOrderUpdatedNotification)->handle(
        new StatusOrderUpdated($order),
    );

    Queue::assertNotPushed(SendExpoPushNotification::class);
});
