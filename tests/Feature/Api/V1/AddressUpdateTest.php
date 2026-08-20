<?php

use App\Models\Address;
use App\Models\User;

function validMobileAddressUpdateData(array $overrides = []): array
{
    return [
        'first_name' => 'Updated',
        'last_name' => 'Customer',
        'phone' => '+352 621 111 111',
        'street' => '10 Updated Street',
        'city' => 'Esch-sur-Alzette',
        'postal_code' => 'L-4000',
        'country' => 'Luxembourg',
        'is_default' => false,
        ...$overrides,
    ];
}

it('updates an address owned by the authenticated mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $address = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressUpdateData([
            'first_name' => 'Original',
            'street' => '1 Original Street',
        ]),
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->patchJson(
            "/api/v1/addresses/{$address->id}",
            validMobileAddressUpdateData(),
        );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $address->id)
        ->assertJsonPath('data.first_name', 'Updated')
        ->assertJsonPath('data.street', '10 Updated Street')
        ->assertJsonPath('data.city', 'Esch-sur-Alzette')
        ->assertJsonPath('data.is_default', false);

    $this->assertDatabaseHas('addresses', [
        'id' => $address->id,
        'user_id' => $user->id,
        'first_name' => 'Updated',
        'street' => '10 Updated Street',
    ]);
});

it('makes an updated address the users only default address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $previousDefault = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressUpdateData([
            'street' => '1 Default Street',
            'is_default' => true,
        ]),
    ]);

    $address = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressUpdateData([
            'street' => '2 Secondary Street',
            'is_default' => false,
        ]),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson(
            "/api/v1/addresses/{$address->id}",
            validMobileAddressUpdateData([
                'street' => '2 New Default Street',
                'is_default' => true,
            ]),
        )
        ->assertOk()
        ->assertJsonPath('data.is_default', true);

    expect($previousDefault->refresh()->is_default)->toBeFalse()
        ->and($address->refresh()->is_default)->toBeTrue();
});

it('does not update another users address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $address = Address::query()->create([
        'user_id' => $otherUser->id,
        ...validMobileAddressUpdateData([
            'first_name' => 'Other',
            'street' => '99 Private Street',
        ]),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson(
            "/api/v1/addresses/{$address->id}",
            validMobileAddressUpdateData(),
        )
        ->assertNotFound();

    expect($address->refresh()->first_name)->toBe('Other')
        ->and($address->street)->toBe('99 Private Street');
});

it('validates mobile address update fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $address = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressUpdateData(),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/addresses/{$address->id}", [
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

it('requires authentication to update a mobile address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $address = Address::query()->create([
        'user_id' => $user->id,
        ...validMobileAddressUpdateData(),
    ]);

    $this
        ->patchJson(
            "/api/v1/addresses/{$address->id}",
            validMobileAddressUpdateData(),
        )
        ->assertUnauthorized();
});
