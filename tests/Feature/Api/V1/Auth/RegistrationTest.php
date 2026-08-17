<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('registers a mobile customer and issues a token', function () {
    Event::fake([Registered::class]);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test Customer',
        'email' => 'CUSTOMER@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'Test phone',
    ], [
        'Accept-Language' => 'fr',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.name', 'Test Customer')
        ->assertJsonPath('data.user.email', 'customer@example.com')
        ->assertJsonPath('data.user.locale', 'fr')
        ->assertJsonPath('data.user.email_verified', false);

    expect($response->json('data.token'))
        ->toBeString()
        ->not->toBeEmpty();

    $user = User::query()
        ->where('email', 'customer@example.com')
        ->firstOrFail();

    expect(Hash::check('password', $user->password))->toBeTrue();

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'Test phone',
    ]);

    Event::assertDispatched(
        Registered::class,
        fn (Registered $event) => $event->user->is($user),
    );
});

it('requires all mobile registration fields', function () {
    $this->postJson('/api/v1/auth/register')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'device_name',
        ]);

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('rejects an email address that is already registered', function () {
    User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
    ]);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Another Customer',
        'email' => 'CUSTOMER@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'Test phone',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('requires password confirmation to match', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
        'device_name' => 'Test phone',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});
