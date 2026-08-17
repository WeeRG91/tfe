<?php

use App\Models\User;

it('revokes only the current mobile token', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $currentToken = $user->createToken('Current phone', ['mobile']);
    $otherToken = $user->createToken('Other device', ['mobile']);

    $this
        ->withToken($currentToken->plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $currentToken->accessToken->id,
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $otherToken->accessToken->id,
    ]);
});

it('rejects logout without a mobile token', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});
