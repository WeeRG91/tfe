<?php

use App\Models\Address;
use App\Models\User;

function mobileAddressForDeletion(User $user, array $overrides = []): Address
{
    return Address::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'phone' => '+352 621 000 000',
        'street' => '1 Main Street',
        'city' => 'Luxembourg',
        'postal_code' => 'L-1111',
        'country' => 'Luxembourg',
        'is_default' => false,
        ...$overrides,
    ]);
}

it('deletes an address owned by the authenticated mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $defaultAddress = mobileAddressForDeletion($user, [
        'is_default' => true,
    ]);
    $address = mobileAddressForDeletion($user, [
        'street' => '2 Secondary Street',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertOk()
        ->assertJsonPath('data.deleted_id', $address->id)
        ->assertJsonPath(
            'data.new_default_address_id',
            $defaultAddress->id,
        );

    $this->assertDatabaseMissing('addresses', [
        'id' => $address->id,
    ]);

    expect($defaultAddress->refresh()->is_default)->toBeTrue();
});

it('promotes another address when deleting the default address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $defaultAddress = mobileAddressForDeletion($user, [
        'is_default' => true,
    ]);
    $replacement = mobileAddressForDeletion($user, [
        'street' => '2 Replacement Street',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/addresses/{$defaultAddress->id}")
        ->assertOk()
        ->assertJsonPath('data.deleted_id', $defaultAddress->id)
        ->assertJsonPath(
            'data.new_default_address_id',
            $replacement->id,
        );

    $this->assertDatabaseMissing('addresses', [
        'id' => $defaultAddress->id,
    ]);

    expect($replacement->refresh()->is_default)->toBeTrue();
});

it('returns no replacement when deleting the users only address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $address = mobileAddressForDeletion($user, [
        'is_default' => true,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertOk()
        ->assertJsonPath('data.deleted_id', $address->id)
        ->assertJsonPath('data.new_default_address_id', null);

    $this->assertDatabaseMissing('addresses', [
        'id' => $address->id,
    ]);
});

it('does not delete another users address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $address = mobileAddressForDeletion($otherUser, [
        'is_default' => true,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('addresses', [
        'id' => $address->id,
        'user_id' => $otherUser->id,
    ]);
});

it('returns not found for a missing mobile address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/addresses/999999')
        ->assertNotFound();
});

it('requires authentication to delete a mobile address', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $address = mobileAddressForDeletion($user);

    $this
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertUnauthorized();
});
