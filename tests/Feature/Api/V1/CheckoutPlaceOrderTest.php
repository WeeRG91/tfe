<?php

use App\Enums\DishCategoryEnum;
use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Dish;
use App\Models\LoyaltyPointTransaction;
use App\Models\RestaurantHour;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('places a cash dine-in order from the authenticated mobile users cart', function () {
    Event::fake([OrderPlacedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 2,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with curry sauce.',
    ]);
    $dish->save();

    $cartItem = $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 2,
        'unit_price' => '14.50',
        'total' => '29.00',
        'spicy_level' => 2,
        'notes' => 'No onions',
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'table_number' => '12',
            'payment_method' => PaymentMethodEnum::CASH->value,
            'notes' => 'Please bring extra napkins.',
            'used_points' => 0,
        ], [
            'Accept-Language' => 'en',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.type.value', OrderTypeEnum::DINE_IN->value)
        ->assertJsonPath('data.type.key', 'dinein')
        ->assertJsonPath('data.table_number', '12')
        ->assertJsonPath('data.status.value', OrderStatusEnum::CONFIRMED->value)
        ->assertJsonPath('data.status.key', 'confirmed')
        ->assertJsonPath('data.payment_method.value', PaymentMethodEnum::CASH->value)
        ->assertJsonPath('data.payment_method.key', 'cash')
        ->assertJsonPath('data.payment_status.value', PaymentStatusEnum::PENDING->value)
        ->assertJsonPath('data.payment_status.key', 'pending')
        ->assertJsonPath('data.notes', 'Please bring extra napkins.')
        ->assertJsonPath('data.subtotal', '29.00')
        ->assertJsonPath('data.discount_total', '0.00')
        ->assertJsonPath('data.delivery_fee', '0.00')
        ->assertJsonPath('data.total', '29.00')
        ->assertJsonPath('data.items.0.item_type', 'dish')
        ->assertJsonPath('data.items.0.item.id', $dish->id)
        ->assertJsonPath('data.items.0.item.name', 'Chicken curry')
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.unit_price', '14.50')
        ->assertJsonPath('data.items.0.line_total', '29.00');

    $orderId = $response->json('data.id');

    expect($response->json('data.order_number'))
        ->toStartWith('ORD-');

    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'user_id' => $user->id,
        'type' => OrderTypeEnum::DINE_IN->value,
        'status' => OrderStatusEnum::CONFIRMED->value,
        'payment_method' => PaymentMethodEnum::CASH->value,
        'subtotal' => '29.00',
        'total_inc_vat' => '29.00',
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 2,
        'unit_price' => '14.50',
        'total_inc_vat' => '29.00',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);

    $this->assertDatabaseHas('loyalty_point_transactions', [
        'user_id' => $user->id,
        'order_id' => $orderId,
        'points' => 87,
        'type' => LoyaltyPointTransactionTypeEnum::EARNED->value,
    ]);

    Event::assertDispatched(OrderPlacedBroadcast::class);
});

it('places a delivery order with an owned address and loyalty discount', function () {
    Event::fake([OrderPlacedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $address = Address::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 000',
        'street' => '1 Delivery Street',
        'city' => 'Luxembourg',
        'postal_code' => 'L-1111',
        'country' => 'Luxembourg',
        'is_default' => true,
    ]);

    LoyaltyPointTransaction::query()->create([
        'user_id' => $user->id,
        'points' => 400,
        'type' => LoyaltyPointTransactionTypeEnum::EARNED,
    ]);

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with curry sauce.',
    ]);
    $dish->save();

    $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 1,
        'unit_price' => '14.50',
        'total' => '14.50',
        'spicy_level' => 1,
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DELIVERY->value,
            'address_id' => $address->id,
            'payment_method' => PaymentMethodEnum::CASH->value,
            'used_points' => 300,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.type.key', 'delivery')
        ->assertJsonPath('data.delivery_address.id', $address->id)
        ->assertJsonPath(
            'data.delivery_address.street',
            '1 Delivery Street',
        )
        ->assertJsonPath('data.subtotal', '14.50')
        ->assertJsonPath('data.discount_total', '5.00')
        ->assertJsonPath('data.delivery_fee', '2.00')
        ->assertJsonPath('data.total', '11.50');

    $orderId = $response->json('data.id');

    $this->assertDatabaseHas('loyalty_point_transactions', [
        'user_id' => $user->id,
        'order_id' => $orderId,
        'points' => 300,
        'type' => LoyaltyPointTransactionTypeEnum::REDEEMED->value,
    ]);

    $this->assertDatabaseHas('loyalty_point_transactions', [
        'user_id' => $user->id,
        'order_id' => $orderId,
        'points' => 34,
        'type' => LoyaltyPointTransactionTypeEnum::EARNED->value,
    ]);
});

it('places a pending takeaway card order without awarding points', function () {
    Event::fake([OrderPlacedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $now = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow($now);

    RestaurantHour::query()->create([
        'weekday' => 1,
        'is_open' => true,
        'opens_at' => '11:00:00',
        'closes_at' => '22:00:00',
        'last_pickup_at' => '21:00:00',
    ]);

    $pickupTime = $now->addHour();

    $dish = Dish::query()->create([
        'price' => '12.00',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 0,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Vegetable noodles',
        'description' => 'Noodles with vegetables.',
    ]);
    $dish->save();

    $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 1,
        'unit_price' => '12.00',
        'total' => '12.00',
        'spicy_level' => 0,
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::TAKEAWAY->value,
            'pickup_time' => $pickupTime->toISOString(),
            'pickup_name' => 'Test Customer',
            'pickup_phone' => '+352 621 000 000',
            'payment_method' => PaymentMethodEnum::CARD->value,
            'used_points' => 0,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.type.key', 'takeaway')
        ->assertJsonPath('data.pickup_name', 'Test Customer')
        ->assertJsonPath('data.pickup_phone', '+352 621 000 000')
        ->assertJsonPath('data.status.key', 'pending')
        ->assertJsonPath('data.payment_method.key', 'card')
        ->assertJsonPath('data.payment_status.key', 'pending')
        ->assertJsonPath('data.confirmed_at', null)
        ->assertJsonPath('data.total', '12.00');

    $orderId = $response->json('data.id');

    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'user_id' => $user->id,
        'type' => OrderTypeEnum::TAKEAWAY->value,
        'status' => OrderStatusEnum::PENDING->value,
        'payment_method' => PaymentMethodEnum::CARD->value,
        'payment_status' => PaymentStatusEnum::PENDING->value,
        'pickup_time' => '2026-09-14 11:00:00',
    ]);

    $this->assertDatabaseMissing('loyalty_point_transactions', [
        'user_id' => $user->id,
        'order_id' => $orderId,
    ]);

    Event::assertNotDispatched(OrderPlacedBroadcast::class);
});

it('rejects placing a mobile order from an empty cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'table_number' => '12',
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cart');

    $this->assertDatabaseCount('orders', 0);
});

it('validates the required mobile checkout fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'cart_id',
            'type',
            'payment_method',
        ]);
});

it('validates fields required by each mobile order type', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('table_number');

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::TAKEAWAY->value,
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'pickup_time',
            'pickup_name',
            'pickup_phone',
        ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DELIVERY->value,
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('address_id');
});

it('rejects another users cart and delivery address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $otherCart = Cart::query()->create([
        'user_id' => $otherUser->id,
    ]);
    $otherAddress = Address::query()->create([
        'user_id' => $otherUser->id,
        'first_name' => 'Other',
        'last_name' => 'Customer',
        'phone' => '+352 621 999 999',
        'street' => '99 Private Street',
        'city' => 'Luxembourg',
        'postal_code' => 'L-9999',
        'country' => 'Luxembourg',
        'is_default' => true,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $otherCart->id,
            'type' => OrderTypeEnum::DELIVERY->value,
            'address_id' => $otherAddress->id,
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'cart_id',
            'address_id',
        ]);
});

it('rolls back a mobile order when loyalty points are insufficient', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with curry sauce.',
    ]);
    $dish->save();

    $cartItem = $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 1,
        'unit_price' => '14.50',
        'total' => '14.50',
        'spicy_level' => 1,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'table_number' => '12',
            'payment_method' => PaymentMethodEnum::CASH->value,
            'used_points' => 300,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('used_points');

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'cart_id' => $cart->id,
    ]);
});

it('requires a verified email to place a mobile order', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->unverified()
        ->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DINE_IN->value,
            'table_number' => '12',
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertForbidden();
});

it('requires authentication to place a mobile order', function () {
    $this
        ->postJson('/api/v1/checkout/orders', [])
        ->assertUnauthorized();
});

it('rejects a takeaway time that is not an available slot', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow($now);

    RestaurantHour::query()->create([
        'weekday' => 1,
        'is_open' => true,
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

    $cart = Cart::query()->create([
        'user_id' => $user->id,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::TAKEAWAY->value,

            // 13:05 is not on a 15-minute boundary.
            'pickup_time' => '2026-09-14T13:05:00+02:00',

            'pickup_name' => 'Test Customer',
            'pickup_phone' => '+352 621 000 000',
            'payment_method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('pickup_time');

    $this->assertDatabaseCount('orders', 0);
});
