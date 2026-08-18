<?php

use App\Models\User;

it('issues a mobile token for valid credentials', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'locale' => 'en',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test phone',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.name', 'Test Customer')
        ->assertJsonPath('data.user.email', 'customer@example.com')
        ->assertJsonPath('data.user.locale', 'en')
        ->assertJsonPath('data.user.email_verified', true);

    expect($response->json('data.token'))
        ->toBeString()
        ->not->toBeEmpty();

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'Test phone',
    ]);
});

it('does not issue a token before two-factor authentication', function () {
    $user = User::factory()->create([
        'email' => 'secured@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test phone',
    ]);

    $response
        ->assertStatus(409)
        ->assertJsonPath('message', 'Two-factor authentication is required.')
        ->assertJsonPath('code', 'two_factor_required');

    expect($response->json('challenge_token'))
        ->toBeString()
        ->not->toBeEmpty();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);
});

it('rejects invalid credentials without issuing a token', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'Test phone',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);
});

it('requires email password and device name', function () {
    $response = $this->postJson('/api/v1/auth/login');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
            'device_name',
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});
