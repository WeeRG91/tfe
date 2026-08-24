<?php

use App\Enums\NotificationTypeEnum;
use App\Models\Notification;
use App\Models\User;

function mobileNotificationForRead(
    User $user,
    array $overrides = [],
): Notification {
    return Notification::query()->create([
        'user_id' => $user->id,
        'type' => NotificationTypeEnum::SYSTEM_ANNOUNCEMENT,
        'title' => 'Announcement',
        'message' => 'A notification for the mobile customer.',
        'data' => [],
        ...$overrides,
    ]);
}

it('marks an owned mobile notification as read', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $notification = mobileNotificationForRead($user);

    $response = $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/notifications/{$notification->id}/read");

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.is_read', true)
        ->assertJsonPath(
            'data.read_at',
            fn ($readAt): bool => is_string($readAt),
        );

    expect($notification->refresh()->read_at)->not->toBeNull();
});

it('keeps an already read mobile notification read', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $readAt = now()->subHour()->startOfSecond();
    $notification = mobileNotificationForRead($user, [
        'read_at' => $readAt,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJsonPath('data.is_read', true)
        ->assertJsonPath('data.read_at', $readAt->toISOString());

    expect($notification->refresh()->read_at->equalTo($readAt))->toBeTrue();
});

it('does not mark another users mobile notification as read', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $notification = mobileNotificationForRead($otherUser);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/notifications/{$notification->id}/read")
        ->assertNotFound();

    expect($notification->refresh()->read_at)->toBeNull();
});

it('requires authentication to mark a mobile notification as read', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $notification = mobileNotificationForRead($user);

    $this
        ->patchJson("/api/v1/notifications/{$notification->id}/read")
        ->assertUnauthorized();
});

it('marks all of the authenticated mobile users notifications as read', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $firstUnread = mobileNotificationForRead($user);
    $secondUnread = mobileNotificationForRead($user);
    $existingReadAt = now()->subDay()->startOfSecond();
    $alreadyRead = mobileNotificationForRead($user, [
        'read_at' => $existingReadAt,
    ]);
    $otherNotification = mobileNotificationForRead($otherUser);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson('/api/v1/notifications/read-all')
        ->assertNoContent();

    expect($firstUnread->refresh()->read_at)->not->toBeNull()
        ->and($secondUnread->refresh()->read_at)->not->toBeNull()
        ->and($alreadyRead->refresh()->read_at->equalTo($existingReadAt))
        ->toBeTrue()
        ->and($otherNotification->refresh()->read_at)->toBeNull();
});

it('requires authentication to mark all mobile notifications as read', function () {
    $this
        ->patchJson('/api/v1/notifications/read-all')
        ->assertUnauthorized();
});
