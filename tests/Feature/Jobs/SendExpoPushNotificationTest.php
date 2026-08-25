<?php

use App\Jobs\SendExpoPushNotification;
use App\Models\ExpoPushToken;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('is processed through the queue', function () {
    $job = new SendExpoPushNotification(
        userId: 1,
        title: 'Order ready',
        body: 'Your order is ready.',
        data: ['order_id' => 123],
    );

    expect($job)->toBeInstanceOf(ShouldQueue::class);
});

it('sends an expo push message to every registered device', function () {
    Http::fake([
        'https://exp.host/--/api/v2/push/send' => Http::response([
            'data' => [
                ['status' => 'ok', 'id' => 'ticket-one'],
                ['status' => 'ok', 'id' => 'ticket-two'],
            ],
        ]),
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $user->expoPushTokens()->createMany([
        [
            'token' => 'ExponentPushToken[first-device]',
            'platform' => 'android',
            'device_name' => 'Android phone',
            'last_used_at' => now(),
        ],
        [
            'token' => 'ExpoPushToken[second-device]',
            'platform' => 'ios',
            'device_name' => 'iPhone',
            'last_used_at' => now(),
        ],
    ]);

    $job = new SendExpoPushNotification(
        userId: $user->id,
        title: 'Order ready',
        body: 'Order #TFE-100 is ready.',
        data: [
            'type' => 'order_ready',
            'order_id' => 100,
            'order_number' => 'TFE-100',
        ],
    );

    $job->handle();

    Http::assertSent(function (Request $request): bool {
        if ($request->url() !== 'https://exp.host/--/api/v2/push/send') {
            return false;
        }

        $messages = $request->data();

        return count($messages) === 2
            && $messages[0] === [
                'to' => 'ExponentPushToken[first-device]',
                'sound' => 'default',
                'title' => 'Order ready',
                'body' => 'Order #TFE-100 is ready.',
                'data' => [
                    'type' => 'order_ready',
                    'order_id' => 100,
                    'order_number' => 'TFE-100',
                ],
            ]
            && $messages[1] === [
                'to' => 'ExpoPushToken[second-device]',
                'sound' => 'default',
                'title' => 'Order ready',
                'body' => 'Order #TFE-100 is ready.',
                'data' => [
                    'type' => 'order_ready',
                    'order_id' => 100,
                    'order_number' => 'TFE-100',
                ],
            ];
    });

    Http::assertSentCount(1);
});

it('does not contact expo when the user has no registered devices', function () {
    Http::fake();

    $user = User::factory()->withoutTwoFactor()->create();

    (new SendExpoPushNotification(
        userId: $user->id,
        title: 'Order ready',
        body: 'Your order is ready.',
        data: ['order_id' => 100],
    ))->handle();

    Http::assertNothingSent();
});

it('removes tokens rejected by expo as unregistered devices', function () {
    Http::fake([
        'https://exp.host/--/api/v2/push/send' => Http::response([
            'data' => [
                ['status' => 'ok', 'id' => 'valid-ticket'],
                [
                    'status' => 'error',
                    'message' => 'The device is not registered.',
                    'details' => ['error' => 'DeviceNotRegistered'],
                ],
            ],
        ]),
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    /** @var ExpoPushToken $validToken */
    $validToken = $user->expoPushTokens()->create([
        'token' => 'ExponentPushToken[valid-device]',
        'platform' => 'android',
        'device_name' => 'Valid phone',
        'last_used_at' => now(),
    ]);
    /** @var ExpoPushToken $invalidToken */
    $invalidToken = $user->expoPushTokens()->create([
        'token' => 'ExponentPushToken[unregistered-device]',
        'platform' => 'android',
        'device_name' => 'Old phone',
        'last_used_at' => now(),
    ]);

    (new SendExpoPushNotification(
        userId: $user->id,
        title: 'Order ready',
        body: 'Your order is ready.',
        data: ['order_id' => 100],
    ))->handle();

    expect(ExpoPushToken::query()->find($validToken->id))->not->toBeNull()
        ->and(ExpoPushToken::query()->find($invalidToken->id))->toBeNull();
});

it('keeps registered tokens when the expo service is unavailable', function () {
    Http::fake([
        'https://exp.host/--/api/v2/push/send' => Http::response(
            ['message' => 'Service unavailable'],
            503,
        ),
    ]);

    $user = User::factory()->withoutTwoFactor()->create();
    /** @var ExpoPushToken $token */
    $token = $user->expoPushTokens()->create([
        'token' => 'ExponentPushToken[temporary-failure-device]',
        'platform' => 'android',
        'device_name' => 'Test phone',
        'last_used_at' => now(),
    ]);

    (new SendExpoPushNotification(
        userId: $user->id,
        title: 'Order ready',
        body: 'Your order is ready.',
        data: ['order_id' => 100],
    ))->handle();

    expect(ExpoPushToken::query()->find($token->id))->not->toBeNull();
});
