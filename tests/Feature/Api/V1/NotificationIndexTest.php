<?php

use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function mobileOrderForNotification(User $user): Order
{
    return Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => OrderStatusEnum::READY,
        'payment_method' => PaymentMethodEnum::CARD,
        'payment_status' => PaymentStatusEnum::PAID,
        'subtotal' => '12.00',
        'discount_total' => '0.00',
        'vat_total' => '1.29',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '12.00',
    ]);
}

function mobileNotification(
    User $user,
    array $overrides = [],
): Notification {
    return Notification::query()->create([
        'user_id' => $user->id,
        'type' => NotificationTypeEnum::SYSTEM_ANNOUNCEMENT,
        'title' => 'Announcement',
        'message' => 'A notification for the mobile customer.',
        'data' => [],
        ...$overrides,
    ]);
}

it('returns only the authenticated mobile users notifications newest first', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForNotification($user);

    $oldestNotification = mobileNotification($user, [
        'created_at' => now()->subDays(2),
        'read_at' => now()->subDay(),
    ]);
    $middleNotification = mobileNotification($user, [
        'notifiable_id' => $order->id,
        'notifiable_type' => Order::class,
        'type' => NotificationTypeEnum::ORDER_CONFIRMED,
        'title' => 'order_confirmed',
        'message' => '',
        'data' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_type' => $order->type->key(),
        ],
        'created_at' => now()->subDay(),
    ]);
    $newestNotification = mobileNotification($user, [
        'notifiable_id' => $order->id,
        'notifiable_type' => Order::class,
        'type' => NotificationTypeEnum::ORDER_READY,
        'title' => 'order_ready',
        'message' => '',
        'data' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_type' => $order->type->key(),
        ],
        'created_at' => now(),
    ]);
    $otherNotification = mobileNotification($otherUser, [
        'created_at' => now()->addMinute(),
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications?per_page=2');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newestNotification->id)
        ->assertJsonPath(
            'data.0.type.value',
            NotificationTypeEnum::ORDER_READY->value,
        )
        ->assertJsonPath('data.0.type.key', 'order_ready')
        ->assertJsonPath('data.0.is_read', false)
        ->assertJsonPath('data.0.data.order_id', $order->id)
        ->assertJsonPath(
            'data.0.data.order_number',
            $order->order_number,
        )
        ->assertJsonPath('data.1.id', $middleNotification->id)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('unread_count', 2)
        ->assertJsonMissing(['id' => $otherNotification->id]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $oldestNotification->id)
        ->assertJsonPath('data.0.is_read', true)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('unread_count', 2);
});

it('filters the authenticated mobile users unread notifications', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $readNotification = mobileNotification($user, [
        'read_at' => now(),
    ]);
    $unreadNotification = mobileNotification($user);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications?filter=unread')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $unreadNotification->id)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('unread_count', 1)
        ->assertJsonMissing(['id' => $readNotification->id]);
});

it('returns an empty paginated notification list for a new mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('unread_count', 0);
});

it('requires authentication to view mobile notifications', function () {
    $this
        ->getJson('/api/v1/notifications')
        ->assertUnauthorized();
});
