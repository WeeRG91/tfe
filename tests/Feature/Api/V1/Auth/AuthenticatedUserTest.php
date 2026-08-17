<?php

use App\Models\User;

it('returns the authenticated mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'locale' => 'en',
    ]);

    $token = $user->createToken('Test phone', ['mobile'])->plainTextToken;

    $response = $this
        ->withToken($token)
        ->getJson('/api/v1/auth/user');

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Test Customer')
        ->assertJsonPath('data.email', 'customer@example.com')
        ->assertJsonPath('data.locale', 'en')
        ->assertJsonPath('data.email_verified', true);
});

it('rejects requests without a mobile token', function () {
    $this->getJson('/api/v1/auth/user')
        ->assertUnauthorized();
});
