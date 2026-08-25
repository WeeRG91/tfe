<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('registers an expo push token for the authenticated mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[test-device-token]',
            'platform' => 'android',
            'device_name' => 'Test phone',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.token',
            'ExponentPushToken[test-device-token]',
        )
        ->assertJsonPath('data.platform', 'android')
        ->assertJsonPath('data.device_name', 'Test phone')
        ->assertJsonPath(
            'data.last_used_at',
            fn ($lastUsedAt): bool => is_string($lastUsedAt),
        );

    $this->assertDatabaseHas('expo_push_tokens', [
        'user_id' => $user->id,
        'token' => 'ExponentPushToken[test-device-token]',
        'platform' => 'android',
        'device_name' => 'Test phone',
    ]);
});

it('updates an existing expo push token without creating a duplicate', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    DB::table('expo_push_tokens')->insert([
        'user_id' => $user->id,
        'token' => 'ExponentPushToken[existing-device-token]',
        'platform' => 'android',
        'device_name' => 'Old device name',
        'last_used_at' => now()->subDay(),
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[existing-device-token]',
            'platform' => 'ios',
            'device_name' => 'Renamed phone',
        ])
        ->assertOk()
        ->assertJsonPath('data.platform', 'ios')
        ->assertJsonPath('data.device_name', 'Renamed phone');

    $this->assertDatabaseCount('expo_push_tokens', 1);
    $this->assertDatabaseHas('expo_push_tokens', [
        'user_id' => $user->id,
        'token' => 'ExponentPushToken[existing-device-token]',
        'platform' => 'ios',
        'device_name' => 'Renamed phone',
    ]);
});

it('moves a reused expo push token to the currently authenticated user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    DB::table('expo_push_tokens')->insert([
        'user_id' => $otherUser->id,
        'token' => 'ExpoPushToken[reassigned-device-token]',
        'platform' => 'android',
        'device_name' => 'Shared phone',
        'last_used_at' => now()->subDay(),
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/push-tokens', [
            'token' => 'ExpoPushToken[reassigned-device-token]',
            'platform' => 'android',
            'device_name' => 'Shared phone',
        ])
        ->assertOk();

    $this->assertDatabaseCount('expo_push_tokens', 1);
    $this->assertDatabaseHas('expo_push_tokens', [
        'user_id' => $user->id,
        'token' => 'ExpoPushToken[reassigned-device-token]',
    ]);
    $this->assertDatabaseMissing('expo_push_tokens', [
        'user_id' => $otherUser->id,
        'token' => 'ExpoPushToken[reassigned-device-token]',
    ]);
});

it('validates mobile expo push token registration fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/push-tokens', [
            'token' => 'not-an-expo-token',
            'platform' => 'windows',
            'device_name' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'token',
            'platform',
            'device_name',
        ]);
});

it('requires authentication to register an expo push token', function () {
    $this
        ->postJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[test-device-token]',
            'platform' => 'android',
            'device_name' => 'Test phone',
        ])
        ->assertUnauthorized();
});

it('deletes an owned expo push token from the mobile device', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    DB::table('expo_push_tokens')->insert([
        'user_id' => $user->id,
        'token' => 'ExponentPushToken[owned-device-token]',
        'platform' => 'android',
        'device_name' => 'Test phone',
        'last_used_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[owned-device-token]',
        ])
        ->assertNoContent();

    $this->assertDatabaseMissing('expo_push_tokens', [
        'user_id' => $user->id,
        'token' => 'ExponentPushToken[owned-device-token]',
    ]);
});

it('does not delete another users expo push token', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    DB::table('expo_push_tokens')->insert([
        'user_id' => $otherUser->id,
        'token' => 'ExponentPushToken[private-device-token]',
        'platform' => 'ios',
        'device_name' => 'Other phone',
        'last_used_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[private-device-token]',
        ])
        ->assertNoContent();

    $this->assertDatabaseHas('expo_push_tokens', [
        'user_id' => $otherUser->id,
        'token' => 'ExponentPushToken[private-device-token]',
    ]);
});

it('requires authentication to delete an expo push token', function () {
    $this
        ->deleteJson('/api/v1/push-tokens', [
            'token' => 'ExponentPushToken[test-device-token]',
        ])
        ->assertUnauthorized();
});
