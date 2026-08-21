<?php

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function mobileOrderForStatus(User $user, array $overrides = []): Order
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

it('returns the authenticated mobile users order status', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForStatus($user);

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->id)
        ->assertJsonPath('data.order_number', $order->order_number)
        ->assertJsonPath('data.status.value', OrderStatusEnum::PENDING->value)
        ->assertJsonPath('data.status.key', 'pending')
        ->assertJsonPath(
            'data.payment_method.value',
            PaymentMethodEnum::CARD->value,
        )
        ->assertJsonPath('data.payment_method.key', 'card')
        ->assertJsonPath(
            'data.payment_status.value',
            PaymentStatusEnum::PENDING->value,
        )
        ->assertJsonPath('data.payment_status.key', 'pending')
        ->assertJsonPath('data.total', '12.00');
});

it('returns the payment status updated by the stripe webhook', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $paidAt = now()->startOfSecond();
    $order = mobileOrderForStatus($user, [
        'status' => OrderStatusEnum::CONFIRMED,
        'payment_status' => PaymentStatusEnum::PAID,
        'confirmed_at' => $paidAt,
        'paid_at' => $paidAt,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.status.key', 'confirmed')
        ->assertJsonPath('data.payment_status.key', 'paid')
        ->assertJsonPath('data.confirmed_at', $paidAt->toISOString());
});

it('does not expose another users order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForStatus($otherUser);

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertNotFound();
});

it('returns not found for a missing mobile order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders/999999')
        ->assertNotFound();
});

it('requires authentication to view a mobile order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $order = mobileOrderForStatus($user);

    $this
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertUnauthorized();
});
