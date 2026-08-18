<?php

use App\Models\User;
use App\Notifications\Auth\MobileVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('resends a mobile verification email', function () {
    Notification::fake();

    $user = User::factory()
        ->withoutTwoFactor()
        ->unverified()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertAccepted()
        ->assertJsonPath(
            'message',
            'Verification link sent.',
        );

    Notification::assertSentTo($user, MobileVerifyEmail::class);
});

it('does not resend verification to a verified user', function () {
    Notification::fake();

    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $token = $user->createToken('Test phone', ['mobile'])
        ->plainTextToken;

    $this
        ->withToken($token)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertNoContent();

    Notification::assertNothingSent();
});

it('requires authentication to resend verification', function () {
    $this
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertUnauthorized();
});

it('verifies an email through a valid signed mobile link', function () {
    Event::fake([Verified::class]);

    $user = User::factory()
        ->withoutTwoFactor()
        ->unverified()
        ->create();

    $verificationUrl = URL::temporarySignedRoute(
        'api.v1.auth.email.verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this
        ->getJson($verificationUrl)
        ->assertOk()
        ->assertJsonPath(
            'message',
            'Email verified successfully.',
        );

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(
        Verified::class,
        fn (Verified $event) => $event->user->is($user),
    );
});

it('rejects a tampered mobile verification link', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->unverified()
        ->create();

    $verificationUrl = URL::temporarySignedRoute(
        'api.v1.auth.email.verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this
        ->getJson($verificationUrl.'&tampered=true')
        ->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('builds a temporary signed mobile verification url', function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->unverified()
        ->create();

    $mail = (new MobileVerifyEmail)->toMail($user);
    $verificationUrl = $mail->actionUrl;

    expect($verificationUrl)
        ->not->toBeNull()
        ->toContain(
            "/api/v1/auth/email/verify/{$user->id}/"
            .sha1($user->getEmailForVerification()),
        );

    expect(
        URL::hasValidSignature(
            Request::create($verificationUrl),
        ),
    )->toBeTrue();
});
