<?php

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function mobileOrderForHistory(User $user, array $overrides = []): Order
{
    return Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => OrderStatusEnum::PENDING,
        'payment_method' => PaymentMethodEnum::CARD,
        'payment_status' => PaymentStatusEnum::PENDING,
        'subtotal' => '12.00',
        'discount_total' => '0.00',
        'vat_total' => '1.29',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '12.00',
        ...$overrides,
    ]);
}

it('returns only the authenticated mobile users orders newest first', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $oldestOrder = mobileOrderForHistory($user, [
        'created_at' => now()->subDays(2),
    ]);
    $middleOrder = mobileOrderForHistory($user, [
        'created_at' => now()->subDay(),
        'status' => OrderStatusEnum::CONFIRMED,
    ]);
    $newestOrder = mobileOrderForHistory($user, [
        'created_at' => now(),
        'status' => OrderStatusEnum::PREPARING,
        'payment_status' => PaymentStatusEnum::PAID,
    ]);
    $otherOrder = mobileOrderForHistory($otherUser, [
        'created_at' => now()->addMinute(),
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders?per_page=2');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newestOrder->id)
        ->assertJsonPath(
            'data.0.created_at',
            $newestOrder->created_at->toISOString(),
        )
        ->assertJsonPath('data.0.status.key', 'preparing')
        ->assertJsonPath('data.0.payment_status.key', 'paid')
        ->assertJsonPath('data.1.id', $middleOrder->id)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonMissing(['id' => $otherOrder->id]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $oldestOrder->id)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 3);
});

it('returns an empty paginated history when the mobile user has no orders', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 0);
});

it('translates mobile order history labels from the accept-language header', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForHistory($user, [
        'status' => OrderStatusEnum::CONFIRMED,
        'payment_status' => PaymentStatusEnum::PAID,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders', [
            'Accept-Language' => 'fr',
        ])
        ->assertOk()
        ->assertJsonPath('data.0.id', $order->id)
        ->assertJsonPath('data.0.status.label', 'Confirmée')
        ->assertJsonPath('data.0.payment_status.label', 'Payé');
});

it('requires authentication to view mobile order history', function () {
    $this
        ->getJson('/api/v1/orders')
        ->assertUnauthorized();
});
