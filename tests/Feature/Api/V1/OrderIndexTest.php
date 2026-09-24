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

it('cursor paginates mobile orders ten at a time', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $orders = collect();

    foreach (range(1, 15) as $index) {
        $orders->push(
            mobileOrderForHistory($user, [
                'created_at' => now()->subMinutes(15 - $index),
            ]),
        );
    }

    $firstResponse = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders');

    $firstResponse
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath(
            'data.0.id',
            $orders->last()->id,
        )
        ->assertJsonPath('meta.per_page', 10);

    $nextCursor = $firstResponse->json(
        'meta.next_cursor',
    );

    expect($nextCursor)->not->toBeNull();

    $secondResponse = $this
        ->withToken($token->plainTextToken)
        ->getJson(
            '/api/v1/orders?'
            .http_build_query([
                'cursor' => $nextCursor,
            ]),
        );

    $secondResponse
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath(
            'data.0.id',
            $orders->get(4)->id,
        )
        ->assertJsonPath('meta.next_cursor', null);

    $firstPageIds = collect(
        $firstResponse->json('data'),
    )->pluck('id');

    $secondPageIds = collect(
        $secondResponse->json('data'),
    )->pluck('id');

    expect(
        $firstPageIds->intersect($secondPageIds),
    )->toBeEmpty();
});

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
        ->getJson('/api/v1/orders');

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $newestOrder->id)
        ->assertJsonPath(
            'data.0.created_at',
            $newestOrder->created_at->toISOString(),
        )
        ->assertJsonPath(
            'data.0.status.key',
            'preparing',
        )
        ->assertJsonPath(
            'data.0.payment_status.key',
            'paid',
        )
        ->assertJsonPath('data.1.id', $middleOrder->id)
        ->assertJsonPath('data.2.id', $oldestOrder->id)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonMissing(['id' => $otherOrder->id]);
});

it('returns an empty paginated history when the mobile user has no orders', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null);
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
