<?php

use App\Models\Address;
use App\Models\User;

function validMobileAddressData(array $overrides = []): array
{
    return [
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 000',
        'street' => '1 Main Street',
        'city' => 'Luxembourg',
        'postal_code' => 'L-1111',
        'country' => 'Luxembourg',
        ...$overrides,
    ];
}

it('creates the authenticated mobile users first address as default', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/addresses', validMobileAddressData());

    $response
        ->assertCreated()
        ->assertJsonPath('data.first_name', 'Test')
        ->assertJsonPath('data.last_name', 'Customer')
        ->assertJsonPath('data.phone', '+352 621 000 000')
        ->assertJsonPath('data.street', '1 Main Street')
        ->assertJsonPath('data.city', 'Luxembourg')
        ->assertJsonPath('data.postal_code', 'L-1111')
        ->assertJsonPath('data.country', 'Luxembourg')
        ->assertJsonPath('data.is_default', true);

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'first_name' => 'Test',
        'street' => '1 Main Street',
        'is_default' => true,
    ]);
});

it('makes a new default address replace the previous default', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $previousDefault = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressData(),
        'is_default' => true,
    ]);

    $otherUser = User::factory()->withoutTwoFactor()->create();
    $otherUsersDefault = Address::query()->create([
        'user_id' => $otherUser->id,
        ...validMobileAddressData([
            'street' => '99 Private Street',
        ]),
        'is_default' => true,
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/addresses', validMobileAddressData([
            'street' => '2 New Default Street',
            'is_default' => true,
        ]));

    $response
        ->assertCreated()
        ->assertJsonPath('data.street', '2 New Default Street')
        ->assertJsonPath('data.is_default', true);

    expect($previousDefault->refresh()->is_default)->toBeFalse()
        ->and($otherUsersDefault->refresh()->is_default)->toBeTrue();
});

it('creates an additional non-default address without changing the default', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $defaultAddress = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressData(),
        'is_default' => true,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/addresses', validMobileAddressData([
            'street' => '3 Secondary Street',
            'is_default' => false,
        ]))
        ->assertCreated()
        ->assertJsonPath('data.is_default', false);

    expect($defaultAddress->refresh()->is_default)->toBeTrue();
});

it('validates mobile address fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/addresses', [
            'first_name' => '',
            'last_name' => '',
            'phone' => '',
            'street' => '',
            'city' => '',
            'postal_code' => '',
            'country' => '',
            'is_default' => 'yes',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'phone',
            'street',
            'city',
            'postal_code',
            'country',
            'is_default',
        ]);
});

it('requires authentication to create a mobile address', function () {
    $this
        ->postJson('/api/v1/addresses', validMobileAddressData())
        ->assertUnauthorized();
});
