<?php

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\LoyaltyPointTransaction;
use App\Models\User;

function mobilePointTransaction(
    User $user,
    array $overrides = [],
): LoyaltyPointTransaction {
    return LoyaltyPointTransaction::query()->create([
        'user_id' => $user->id,
        'order_id' => null,
        'points' => 100,
        'type' => LoyaltyPointTransactionTypeEnum::EARNED,
        'description' => 'Points earned',
        ...$overrides,
    ]);
}

it('returns the latest ten point transactions and a cursor for more', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();

    $token = $user->createToken('Test phone', ['mobile']);

    foreach (range(1, 15) as $index) {
        mobilePointTransaction($user, [
            'points' => $index,
            'created_at' => now()->subMinutes(15 - $index),
        ]);
    }

    $otherTransaction = mobilePointTransaction($otherUser, [
        'created_at' => now()->addMinute(),
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/loyalty-point-transactions');

    $response
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('data.0.points', 15)
        ->assertJsonPath('data.0.type.value', 1)
        ->assertJsonPath('data.0.type.key', 'earned')
        ->assertJsonPath('data.0.type.label', 'Earned')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonMissing(['id' => $otherTransaction->id]);

    $nextCursor = $response->json('meta.next_cursor');

    expect($nextCursor)->not->toBeNull();

    $secondResponse = $this
        ->withToken($token->plainTextToken)
        ->getJson(
            '/api/v1/loyalty-point-transactions?'
            .http_build_query(['cursor' => $nextCursor]),
        );

    $secondResponse
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null);

    $firstPageIds = collect($response->json('data'))->pluck('id');
    $secondPageIds = collect($secondResponse->json('data'))->pluck('id');

    expect($firstPageIds->intersect($secondPageIds))->toBeEmpty();
});

it('returns an empty transaction history for a user without points activity', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/loyalty-point-transactions')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.next_cursor', null);
});

it('requires authentication to view point transactions', function () {
    $this
        ->getJson('/api/v1/loyalty-point-transactions')
        ->assertUnauthorized();
});

it('returns the type of every point transaction', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $types = [
        LoyaltyPointTransactionTypeEnum::EARNED,
        LoyaltyPointTransactionTypeEnum::REDEEMED,
        LoyaltyPointTransactionTypeEnum::REFUNDED,
        LoyaltyPointTransactionTypeEnum::REVERSED,
    ];

    foreach ($types as $index => $type) {
        mobilePointTransaction($user, [
            'type' => $type,
            'created_at' => now()->addMinutes($index),
        ]);
    }

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/loyalty-point-transactions');

    $response
        ->assertOk()
        ->assertJsonCount(4, 'data');

    $returnedTypes = collect($response->json('data'))
        ->pluck('type.key')
        ->all();

    expect($returnedTypes)->toBe([
        'reversed',
        'refunded',
        'redeemed',
        'earned',
    ]);
});
