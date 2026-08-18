<?php

use App\Models\User;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

it('returns disabled two-factor status', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->getJson('/api/v1/auth/two-factor')
        ->assertOk()
        ->assertJsonPath('data.enabled', false)
        ->assertJsonPath('data.setup_pending', false);
});

it('returns pending two-factor setup status', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->getJson('/api/v1/auth/two-factor')
        ->assertOk()
        ->assertJsonPath('data.enabled', false)
        ->assertJsonPath('data.setup_pending', true);
});

it('returns enabled two-factor status', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->getJson('/api/v1/auth/two-factor')
        ->assertOk()
        ->assertJsonPath('data.enabled', true)
        ->assertJsonPath('data.setup_pending', false);
});

it('requires authentication to view two-factor status', function () {
    $this
        ->getJson('/api/v1/auth/two-factor')
        ->assertUnauthorized();
});

it('starts two-factor setup after current password confirmation', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $response = $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor', [
            'current_password' => 'password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.enabled', false)
        ->assertJsonPath('data.setup_pending', true)
        ->assertJsonMissingPath('data.recovery_codes');

    expect($response->json('data.secret'))
        ->toBeString()
        ->not->toBeEmpty();

    expect($response->json('data.otpauth_url'))
        ->toBeString()
        ->toStartWith('otpauth://totp/');

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and(
            Fortify::currentEncrypter()->decrypt(
                $user->two_factor_secret,
            ),
        )->toBe($response->json('data.secret'));
});

it('rejects starting two-factor setup with an invalid password', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor', [
            'current_password' => 'wrong-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

it('does not expose setup secrets when two-factor authentication is already enabled', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('existing-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['existing-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor', [
            'current_password' => 'password',
        ])
        ->assertConflict()
        ->assertJsonMissingPath('data.secret')
        ->assertJsonMissingPath('data.otpauth_url')
        ->assertJsonMissingPath('data.recovery_codes');
});

it('confirms two-factor setup and returns recovery codes once', function () {
    $googleTwoFactor = new Google2FA;
    $secret = $googleTwoFactor->generateSecretKey();
    $recoveryCodes = [
        'recovery-code-1',
        'recovery-code-2',
        'recovery-code-3',
        'recovery-code-4',
        'recovery-code-5',
        'recovery-code-6',
        'recovery-code-7',
        'recovery-code-8',
    ];

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => null,
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => $googleTwoFactor->getCurrentOtp($secret),
        ])
        ->assertOk()
        ->assertJsonPath('data.enabled', true)
        ->assertJsonPath('data.setup_pending', false)
        ->assertJsonPath('data.recovery_codes', $recoveryCodes)
        ->assertJsonMissingPath('data.secret')
        ->assertJsonMissingPath('data.otpauth_url');

    expect($user->refresh()->two_factor_confirmed_at)
        ->not->toBeNull();
});

it('rejects an invalid confirmation code and keeps setup pending', function () {
    $googleTwoFactor = new Google2FA;
    $secret = $googleTwoFactor->generateSecretKey();
    $validCode = $googleTwoFactor->getCurrentOtp($secret);
    $invalidCode = $validCode === '000000'
        ? '000001'
        : '000000';

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => $invalidCode,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code')
        ->assertJsonMissingPath('data.recovery_codes');

    expect($user->refresh()->two_factor_confirmed_at)
        ->toBeNull()
        ->and($user->hasEnabledTwoFactorAuthentication())
        ->toBeFalse();
});

it('disables two-factor authentication after current password confirmation', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('existing-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['existing-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->deleteJson('/api/v1/auth/two-factor', [
            'current_password' => 'password',
        ])
        ->assertNoContent();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and($user->hasEnabledTwoFactorAuthentication())
        ->toBeFalse();
});

it('rejects disabling two-factor authentication with an invalid password', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('existing-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['existing-recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->deleteJson('/api/v1/auth/two-factor', [
            'current_password' => 'wrong-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->not->toBeNull()
        ->and($user->hasEnabledTwoFactorAuthentication())
        ->toBeTrue();
});

it('regenerates recovery codes after current password confirmation', function () {
    $oldRecoveryCodes = [
        'old-recovery-code-1',
        'old-recovery-code-2',
    ];

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('existing-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode($oldRecoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $response = $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor/recovery-codes', [
            'current_password' => 'password',
        ]);

    $response
        ->assertOk()
        ->assertJsonCount(8, 'data.recovery_codes')
        ->assertJsonMissingPath('data.secret')
        ->assertJsonMissingPath('data.otpauth_url');

    $newRecoveryCodes = $response->json('data.recovery_codes');

    expect($newRecoveryCodes)
        ->toBeArray()
        ->toHaveCount(8)
        ->not->toContain('old-recovery-code-1')
        ->not->toContain('old-recovery-code-2')
        ->and($user->refresh()->recoveryCodes())
        ->toBe($newRecoveryCodes);
});

it('rejects regenerating recovery codes with an invalid password', function () {
    $oldRecoveryCodes = ['existing-recovery-code'];

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('existing-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode($oldRecoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor/recovery-codes', [
            'current_password' => 'wrong-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password')
        ->assertJsonMissingPath('data.recovery_codes');

    expect($user->refresh()->recoveryCodes())
        ->toBe($oldRecoveryCodes);
});

it('rejects regenerating recovery codes when two-factor authentication is disabled', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/two-factor/recovery-codes', [
            'current_password' => 'password',
        ])
        ->assertConflict()
        ->assertJsonMissingPath('data.recovery_codes');
});

it('cancels an unfinished two-factor setup after current password confirmation', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()
            ->encrypt('pending-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()
            ->encrypt(json_encode(['pending-recovery-code'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->deleteJson('/api/v1/auth/two-factor', [
            'current_password' => 'password',
        ])
        ->assertNoContent();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and($user->hasEnabledTwoFactorAuthentication())
        ->toBeFalse();
});
