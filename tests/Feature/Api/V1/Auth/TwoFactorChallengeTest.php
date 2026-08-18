<?php

use App\Models\User;
use App\Services\Auth\MobileTwoFactorChallengeStore;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

it('issues a mobile token for a valid authenticator code', function () {
    $googleTwoFactor = new Google2FA;
    $secret = $googleTwoFactor->generateSecretKey();

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $challengeStore = app(
        MobileTwoFactorChallengeStore::class,
    );

    $challengeToken = $challengeStore->create(
        $user,
        'Test phone',
    );

    $response = $this->postJson(
        '/api/v1/auth/two-factor-challenge',
        [
            'challenge_token' => $challengeToken,
            'code' => $googleTwoFactor->getCurrentOtp($secret),
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);

    expect($response->json('data.token'))
        ->toBeString()
        ->not->toBeEmpty();

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'Test phone',
    ]);

    expect(
        $challengeStore->find($challengeToken),
    )->toBeNull();
});

it('issues a mobile token and replaces a valid recovery code', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode([
                'recovery-code-one',
                'recovery-code-two',
            ])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $challengeStore = app(
        MobileTwoFactorChallengeStore::class,
    );

    $challengeToken = $challengeStore->create(
        $user,
        'Recovery phone',
    );

    $response = $this->postJson(
        '/api/v1/auth/two-factor-challenge',
        [
            'challenge_token' => $challengeToken,
            'recovery_code' => 'recovery-code-one',
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id);

    expect($response->json('data.token'))
        ->toBeString()
        ->not->toBeEmpty();

    expect($user->refresh()->recoveryCodes())
        ->not->toContain('recovery-code-one')
        ->toContain('recovery-code-two');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'Recovery phone',
    ]);

    expect(
        $challengeStore->find($challengeToken),
    )->toBeNull();
});

it('rejects an invalid recovery code without consuming the challenge', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['valid-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $challengeStore = app(
        MobileTwoFactorChallengeStore::class,
    );

    $challengeToken = $challengeStore->create(
        $user,
        'Test phone',
    );

    $this->postJson(
        '/api/v1/auth/two-factor-challenge',
        [
            'challenge_token' => $challengeToken,
            'recovery_code' => 'incorrect-recovery-code',
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recovery_code');

    $this->assertDatabaseCount('personal_access_tokens', 0);

    expect(
        $challengeStore->find($challengeToken),
    )->not->toBeNull();
});

it('rejects an expired two-factor challenge', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $challengeStore = app(
        MobileTwoFactorChallengeStore::class,
    );

    $challengeToken = $challengeStore->create(
        $user,
        'Test phone',
    );

    $this->travel(6)->minutes();

    $this->postJson(
        '/api/v1/auth/two-factor-challenge',
        [
            'challenge_token' => $challengeToken,
            'recovery_code' => 'any-code',
        ],
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('challenge_token');

    $this->assertDatabaseCount('personal_access_tokens', 0);

    expect(
        $challengeStore->find($challengeToken),
    )->toBeNull();
});
