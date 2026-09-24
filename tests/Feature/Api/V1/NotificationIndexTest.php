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

it('cursor paginates mobile notifications ten at a time', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    foreach (range(1, 15) as $index) {
        mobileNotification($user, [
            'title' => "Notification {$index}",
            'created_at' => now()->subMinutes(15 - $index),
        ]);
    }

    $firstResponse = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications');

    $firstResponse
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('data.0.title', 'Notification 15')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('unread_count', 15);

    $nextCursor = $firstResponse->json('meta.next_cursor');

    expect($nextCursor)->not->toBeNull();

    $secondResponse = $this
        ->withToken($token->plainTextToken)
        ->getJson(
            '/api/v1/notifications?'
            .http_build_query(['cursor' => $nextCursor]),
        );

    $secondResponse
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('data.0.title', 'Notification 5')
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonPath('unread_count', 15);

    $firstPageIds = collect($firstResponse->json('data'))
        ->pluck('id');

    $secondPageIds = collect($secondResponse->json('data'))
        ->pluck('id');

    expect(
        $firstPageIds->intersect($secondPageIds),
    )->toBeEmpty();
});

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
        ->getJson('/api/v1/notifications');

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data')
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
        ->assertJsonPath('data.2.id', $oldestNotification->id)
        ->assertJsonPath('data.2.is_read', true)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonPath('unread_count', 2)
        ->assertJsonMissing(['id' => $otherNotification->id]);
});

it('returns an empty paginated notification list for a new mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonPath('unread_count', 0);
});

it('requires authentication to view mobile notifications', function () {
    $this
        ->getJson('/api/v1/notifications')
        ->assertUnauthorized();
});
