<?php

use App\Models\User;
use App\Notifications\Auth\MobileResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('sends a mobile password reset link to a registered user', function () {
    Notification::fake();

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
        'locale' => 'fr',
    ]);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'CUSTOMER@example.com',
    ])
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'If an account exists for this email, a password reset link has been sent.',
        );

    Notification::assertSentTo(
        $user,
        MobileResetPassword::class,
        function (MobileResetPassword $notification) use ($user): bool {
            $actionUrl = $notification->toMail($user)->actionUrl;

            expect($actionUrl)->toStartWith(
                'tfemobile://auth/reset-password?',
            );

            parse_str(
                (string) parse_url($actionUrl, PHP_URL_QUERY),
                $query,
            );

            expect($query['token'] ?? null)->toBe($notification->token)
                ->and($query['email'] ?? null)->toBe($user->email);

            return true;
        },
    );
});

it('does not reveal whether a password reset email is registered', function () {
    Notification::fake();

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'missing@example.com',
    ])
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'If an account exists for this email, a password reset link has been sent.',
        );

    Notification::assertNothingSent();
});

it('requires a valid email to request a mobile password reset', function () {
    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'not-an-email',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('resets a mobile password with a valid token and revokes existing tokens', function () {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
        'password' => Hash::make('old-password'),
    ]);
    $user->createToken('Existing phone', ['mobile']);

    $resetToken = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $resetToken,
        'email' => 'CUSTOMER@example.com',
        'password' => 'New-password-123!',
        'password_confirmation' => 'New-password-123!',
    ])
        ->assertOk()
        ->assertJsonPath(
            'message',
            'Password reset successfully.',
        );

    $user->refresh();

    expect(Hash::check('New-password-123!', $user->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);

    Event::assertDispatched(PasswordReset::class);
});

it('rejects an invalid mobile password reset token', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'customer@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'New-password-123!',
        'password_confirmation' => 'New-password-123!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});

it('requires a confirmed password when resetting a mobile password', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $resetToken = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $resetToken,
        'email' => $user->email,
        'password' => 'New-password-123!',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('requires the token email and password reset fields', function () {
    $this->postJson('/api/v1/auth/reset-password')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'token',
            'email',
            'password',
        ]);
});
