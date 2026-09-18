<?php

use App\Actions\Client\Payment\Commands\CreatePaymentIntent;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;
use Mockery\MockInterface;

function mobileOrderForPayment(User $user, array $overrides = []): Order
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

it('creates a payment intent for the authenticated mobile users card order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForPayment($user);

    $this->mock(
        CreatePaymentIntent::class,
        function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('execute')
                ->once()
                ->withArgs(
                    fn (Order $candidate): bool => $candidate->is($order),
                )
                ->andReturn([
                    'message' => 'Payment initialized.',
                    'client_secret' => 'pi_test_secret_mobile',
                ]);
        },
    );

    $this
        ->withToken($token->plainTextToken)
        ->postJson(
            "/api/v1/checkout/orders/{$order->id}/payment-intent",
        )
        ->assertOk()
        ->assertJsonPath(
            'data.client_secret',
            'pi_test_secret_mobile',
        );
});

it('does not create a payment intent for another users order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForPayment($otherUser);

    $this->mock(
        CreatePaymentIntent::class,
        fn (MockInterface $mock) => $mock
            ->shouldNotReceive('execute'),
    );

    $this
        ->withToken($token->plainTextToken)
        ->postJson(
            "/api/v1/checkout/orders/{$order->id}/payment-intent",
        )
        ->assertForbidden();
});

it('rejects a payment intent for a cash order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForPayment($user, [
        'payment_method' => PaymentMethodEnum::CASH,
        'status' => OrderStatusEnum::CONFIRMED,
        'confirmed_at' => now(),
    ]);

    $this->mock(
        CreatePaymentIntent::class,
        fn (MockInterface $mock) => $mock
            ->shouldNotReceive('execute'),
    );

    $this
        ->withToken($token->plainTextToken)
        ->postJson(
            "/api/v1/checkout/orders/{$order->id}/payment-intent",
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('payment_method');
});

it('does not create another payment intent for a paid order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $order = mobileOrderForPayment($user, [
        'payment_status' => PaymentStatusEnum::PAID,
        'paid_at' => now(),
    ]);

    $this->mock(
        CreatePaymentIntent::class,
        fn (MockInterface $mock) => $mock
            ->shouldNotReceive('execute'),
    );

    $this
        ->withToken($token->plainTextToken)
        ->postJson(
            "/api/v1/checkout/orders/{$order->id}/payment-intent",
        )
        ->assertForbidden();
});

it('requires authentication to create a mobile payment intent', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $order = mobileOrderForPayment($user);

    $this
        ->postJson(
            "/api/v1/checkout/orders/{$order->id}/payment-intent",
        )
        ->assertUnauthorized();
});
