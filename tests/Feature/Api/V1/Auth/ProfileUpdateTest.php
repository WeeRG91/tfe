<?php

use App\Models\User;
use App\Notifications\Auth\MobileVerifyEmail;
use Illuminate\Support\Facades\Notification;

it('updates the authenticated mobile user profile', function () {
    Notification::fake();

    $user = User::factory()->withoutTwoFactor()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'locale' => 'en',
    ]);

    $token = $user->createToken('Test phone', ['mobile']);

    $response = $this
        ->withToken($token->plainTextToken)
        ->patchJson('/api/v1/auth/user', [
            'name' => 'Updated Customer',
            'email' => 'UPDATED@example.com',
            'locale' => 'fr',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Updated Customer')
        ->assertJsonPath('data.email', 'updated@example.com')
        ->assertJsonPath('data.locale', 'fr')
        ->assertJsonPath('data.email_verified', false);

    $user->refresh();

    expect($user->name)->toBe('Updated Customer')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->locale)->toBe('fr')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, MobileVerifyEmail::class);
});

it('keeps email verification when the email address is unchanged', function () {
    Notification::fake();

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
        'email_verified_at' => now(),
        'locale' => 'en',
    ]);

    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson('/api/v1/auth/user', [
            'name' => 'Updated Customer',
            'email' => 'CUSTOMER@example.com',
            'locale' => 'lb',
        ])
        ->assertOk()
        ->assertJsonPath('data.email', 'customer@example.com')
        ->assertJsonPath('data.locale', 'lb')
        ->assertJsonPath('data.email_verified', true);

    expect($user->refresh()->email_verified_at)->not->toBeNull();

    Notification::assertNothingSent();
});

it('rejects an email address used by another user', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
    ]);
    User::factory()->withoutTwoFactor()->create([
        'email' => 'taken@example.com',
    ]);

    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson('/api/v1/auth/user', [
            'name' => 'Test Customer',
            'email' => 'TAKEN@example.com',
            'locale' => 'en',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect($user->refresh()->email)->toBe('customer@example.com');
});

it('requires valid mobile profile fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson('/api/v1/auth/user', [
            'name' => '',
            'email' => 'not-an-email',
            'locale' => 'unsupported',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'locale',
        ]);
});

it('requires authentication to update a mobile profile', function () {
    $this->patchJson('/api/v1/auth/user', [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'locale' => 'en',
    ])->assertUnauthorized();
});
