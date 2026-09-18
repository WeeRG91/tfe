<?php

use App\Enums\DeliveryTypeEnum;
use App\Enums\DrinkCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\DeliveryCompany;
use App\Models\Drink;
use App\Models\Order;
use App\Models\RestaurantHour;
use App\Models\User;
use Carbon\CarbonImmutable;
use App\Events\OrderPlacedBroadcast;
use Illuminate\Support\Facades\Event;

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

    Event::fake([
        OrderPlacedBroadcast::class,
    ]);
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

it('allows adding a drink while the restaurant is closed', function () {
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

    $this->assertDatabaseHas('cart_items', [
        'item_id' => $drink->id,
        'quantity' => 1,
    ]);
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

it('allows a future takeaway order while the restaurant is closed', function () {
    $sunday = RestaurantHour::query()->create([
        'weekday' => 7,
        'is_open' => true,
    ]);

    $sunday->periods()->create([
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

    $cart = Cart::query()
        ->where('user_id', $user->id)
        ->sole();

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::TAKEAWAY->value,
            'pickup_time' =>
                '2026-09-13T12:00:00+02:00',
            'pickup_name' => 'Test Customer',
            'pickup_phone' => '+352 621 000 000',
            'payment_method' =>
                PaymentMethodEnum::CASH->value,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'type' => OrderTypeEnum::TAKEAWAY->value,
        'pickup_name' => 'Test Customer',
    ]);

    Event::assertDispatched(
        OrderPlacedBroadcast::class,
    );
});

it('allows company delivery during closure for an available date', function () {
    config()->set(
        'restaurant.delivery.company.enabled',
        true,
    );

    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $company->deliveryDates()->create([
        'delivery_date' => '2026-09-14',
        'is_available' => true,
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

    $cart = Cart::query()
        ->where('user_id', $user->id)
        ->sole();

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/checkout/orders', [
            'cart_id' => $cart->id,
            'type' => OrderTypeEnum::DELIVERY->value,
            'delivery_type' =>
                DeliveryTypeEnum::COMPANY->value,
            'delivery_company_id' => $company->id,
            'delivery_date' => '2026-09-14',
            'payment_method' =>
                PaymentMethodEnum::CASH->value,
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.delivery_type',
            DeliveryTypeEnum::COMPANY->value,
        )
        ->assertJsonPath(
            'data.delivery_company.id',
            $company->id,
        )
        ->assertJsonPath(
            'data.delivery_company.name',
            'BMS',
        )
        ->assertJsonPath(
            'data.delivery_date',
            '2026-09-14',
        )
        ->assertJsonPath(
            'data.delivery_fee',
            '0.00',
        )
        ->assertJsonPath('data.total', '4.50');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'type' => OrderTypeEnum::DELIVERY->value,
        'delivery_type' =>
            DeliveryTypeEnum::COMPANY->value,
        'delivery_company_id' => $company->id,
        'delivery_company_name' => 'BMS',
        'address_id' => null,
        'delivery_fee' => 0,
    ]);

    $order = $user->orders()->sole();

    expect($order->delivery_date->format('Y-m-d'))
        ->toBe('2026-09-14');
});

it('allows reordering to a company during closure', function () {
    config()->set(
        'restaurant.delivery.company.enabled',
        true,
    );

    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $company->deliveryDates()->create([
        'delivery_date' => '2026-09-14',
        'is_available' => true,
    ]);

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $drink = closedOrderingDrink();

    $originalOrder = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_method' => PaymentMethodEnum::CASH,
        'payment_status' => PaymentStatusEnum::PAID,
        'subtotal' => '4.50',
        'discount_total' => '0.00',
        'vat_total' => '0.78',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '4.50',
        'confirmed_at' => now(),
        'completed_at' => now(),
    ]);

    $originalOrder->items()->create([
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 1,
        'unit_price' => '4.50',
        'vat_rate' => '21.00',
        'vat_amount' => '0.78',
        'total_inc_vat' => '4.50',
    ]);

    $this
        ->actingAs($user)
        ->postJson(
            route('order.confirm-reorder'),
            [
                'order_id' => $originalOrder->id,
                'type' => OrderTypeEnum::DELIVERY->value,
                'delivery_type' =>
                    DeliveryTypeEnum::COMPANY->value,
                'delivery_company_id' => $company->id,
                'delivery_date' => '2026-09-14',
                'payment_method' =>
                    PaymentMethodEnum::CASH->value,
                'used_points' => 0,
            ],
        )
        ->assertOk();

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'type' => OrderTypeEnum::DELIVERY->value,
        'delivery_type' =>
            DeliveryTypeEnum::COMPANY->value,
        'delivery_company_id' => $company->id,
        'delivery_company_name' => 'BMS',
        'address_id' => null,
        'delivery_fee' => 0,
    ]);

    $newOrder = $user->orders()
        ->where('id', '!=', $originalOrder->id)
        ->sole();

    expect($newOrder->delivery_date->format('Y-m-d'))
        ->toBe('2026-09-14')
        ->and($user->orders()->count())->toBe(2)
        ->and($user->orders()->count())->toBe(2);
});

it('rejects own address reorder during closure', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $address = Address::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 000',
        'street' => '1 Delivery Street',
        'city' => 'Arlon',
        'postal_code' => '6700',
        'country' => 'Belgium',
        'is_default' => true,
    ]);

    $drink = closedOrderingDrink();

    $originalOrder = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_method' => PaymentMethodEnum::CASH,
        'payment_status' => PaymentStatusEnum::PAID,
        'subtotal' => '4.50',
        'discount_total' => '0.00',
        'vat_total' => '0.78',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '4.50',
        'confirmed_at' => now(),
        'completed_at' => now(),
    ]);

    $originalOrder->items()->create([
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 1,
        'unit_price' => '4.50',
        'vat_rate' => '21.00',
        'vat_amount' => '0.78',
        'total_inc_vat' => '4.50',
    ]);

    $this
        ->actingAs($user)
        ->postJson(
            route('order.confirm-reorder'),
            [
                'order_id' => $originalOrder->id,
                'type' => OrderTypeEnum::DELIVERY->value,
                'delivery_type' =>
                    DeliveryTypeEnum::OWN_ADDRESS->value,
                'address_id' => $address->id,
                'payment_method' =>
                    PaymentMethodEnum::CASH->value,
                'used_points' => 0,
            ],
        )
        ->assertConflict()
        ->assertJsonPath(
            'code',
            'restaurant_closed',
        );

    expect($user->orders()->count())->toBe(1);
});
