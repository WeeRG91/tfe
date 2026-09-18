<?php

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\DeliveryCompany;
use App\Models\LoyaltyPointTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('returns the authenticated mobile users checkout options', function () {
    config()->set(
        'restaurant.delivery.company.enabled',
        true,
    );

    config()->set(
        'restaurant.timezone',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-16 10:00:00',
            'Europe/Luxembourg',
        ),
    );

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $secondaryAddress = Address::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 001',
        'street' => '2 Secondary Street',
        'city' => 'Luxembourg',
        'postal_code' => 'L-2222',
        'country' => 'Luxembourg',
        'is_default' => false,
    ]);

    $defaultAddress = Address::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 000',
        'street' => '1 Default Street',
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

    LoyaltyPointTransaction::query()->create([
        'user_id' => $user->id,
        'points' => 50,
        'type' => LoyaltyPointTransactionTypeEnum::REDEEMED,
    ]);

    $company = DeliveryCompany::query()->create([
        'name' => 'BMS',
        'is_active' => true,
        'minimum_advance_days' => 2,
    ]);

    $company->deliveryDates()->createMany([
        [
            'delivery_date' => '2026-09-17',
            'is_available' => true,
        ],
        [
            'delivery_date' => '2026-09-18',
            'is_available' => true,
        ],
        [
            'delivery_date' => '2026-09-19',
            'is_available' => false,
        ],
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/checkout', [
            'Accept-Language' => 'en',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.cart.id', $cart->id)
        ->assertJsonPath('data.cart.summary.quantity', 0)
        ->assertJsonPath('data.cart.summary.total', '0.00')
        ->assertJsonPath('data.addresses.0.id', $defaultAddress->id)
        ->assertJsonPath('data.addresses.0.is_default', true)
        ->assertJsonPath('data.addresses.1.id', $secondaryAddress->id)
        ->assertJsonPath('data.order_types.0.value', 1)
        ->assertJsonPath('data.order_types.0.key', 'dinein')
        ->assertJsonPath('data.order_types.1.key', 'takeaway')
        ->assertJsonPath('data.order_types.2.key', 'delivery')
        ->assertJsonPath(
            'data.delivery_options.own_address.fee',
            '2.00',
        )
        ->assertJsonPath(
            'data.delivery_options.own_address.postal_codes.0',
            '6700',
        )
        ->assertJsonPath(
            'data.delivery_options.company.enabled',
            true,
        )
        ->assertJsonPath(
            'data.delivery_options.company.fee',
            '0.00',
        )
        ->assertJsonPath(
            'data.delivery_options.company.companies.0.id',
            $company->id,
        )
        ->assertJsonPath(
            'data.delivery_options.company.companies.0.name',
            'BMS',
        )
        ->assertJsonPath(
            'data.delivery_options.company.companies.0.dates.0',
            '2026-09-18',
        )
        ->assertJsonCount(
            1,
            'data.delivery_options.company.companies.0.dates',
        )
        ->assertJsonPath('data.payment_methods.0.value', 1)
        ->assertJsonPath('data.payment_methods.0.key', 'cash')
        ->assertJsonPath('data.payment_methods.1.key', 'card')
        ->assertJsonPath('data.loyalty_points.balance', 350)
        ->assertJsonPath('data.loyalty_points.redemption_options.0.points', 300)
        ->assertJsonPath('data.loyalty_points.redemption_options.0.discount', '5.00')
        ->assertJsonPath('data.loyalty_points.redemption_options.1.points', 550)
        ->assertJsonPath('data.loyalty_points.redemption_options.1.discount', '10.00')
        ->assertJsonPath('data.delivery_fee', '2.00');
});

it('does not expose another users checkout data', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    Address::query()->create([
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

    LoyaltyPointTransaction::query()->create([
        'user_id' => $otherUser->id,
        'points' => 500,
        'type' => LoyaltyPointTransactionTypeEnum::EARNED,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/checkout')
        ->assertOk()
        ->assertJsonCount(0, 'data.addresses')
        ->assertJsonPath('data.loyalty_points.balance', 0);
});

it('requires authentication to view mobile checkout options', function () {
    $this->getJson('/api/v1/checkout')
        ->assertUnauthorized();
});
