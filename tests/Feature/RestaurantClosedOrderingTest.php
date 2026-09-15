<?php

use App\Enums\DrinkCategoryEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Cart;
use App\Models\Drink;
use App\Models\RestaurantHour;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config()->set(
        'restaurant.allow_orders_while_closed',
        false,
    );

    config()->set(
        'restaurant.timezone',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-12 12:00:00',
            'Europe/Luxembourg',
        ),
    );
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function closedOrderingDrink(): Drink
{
    $drink = Drink::query()->create([
        'price' => '4.50',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);

    $drink->translateOrNew('en')->fill([
        'name' => 'Test drink',
        'description' => 'Test drink description.',
    ]);

    $drink->save();

    return $drink;
}

it('rejects adding a drink while the restaurant is closed', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken(
        'Test phone',
        ['mobile'],
    );

    $drink = closedOrderingDrink();

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'drink_id' => $drink->id,
            'quantity' => 1,
        ])
        ->assertConflict()
        ->assertJsonPath('code', 'restaurant_closed')
        ->assertJsonPath(
            'message',
            'The restaurant is currently closed.',
        );

    $this->assertDatabaseCount('cart_items', 0);
});

it('allows adding a drink during opening hours', function () {
    $day = RestaurantHour::query()->create([
        'weekday' => 6,
        'is_open' => true,
    ]);

    $day->periods()->create([
        'position' => 1,
        'opens_at' => '11:00:00',
        'closes_at' => '22:00:00',
        'last_pickup_at' => '21:00:00',
    ]);

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken(
        'Test phone',
        ['mobile'],
    );

    $drink = closedOrderingDrink();

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'drink_id' => $drink->id,
            'quantity' => 1,
        ])
        ->assertOk();

    $this->assertDatabaseCount('cart_items', 1);
});

it('rejects final order creation while closed', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken(
        'Test phone',
        ['mobile'],
    );

    $cart = Cart::query()->create([
        'user_id' => $user->id,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'table_number' => '12',
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertConflict()
        ->assertJsonPath('code', 'restaurant_closed');

    $this->assertDatabaseCount('orders', 0);
});
