<?php

use App\Models\RestaurantHour;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $user = User::factory()
        ->withoutTwoFactor()
        ->create();

    $permission = Permission::findOrCreate(
        'admin.access',
        'web',
    );

    $user->givePermissionTo($permission);

    $this->actingAs($user);
});

function regularHoursPayload(array $overrides = []): array
{
    $hours = [];

    for ($weekday = 1; $weekday <= 7; $weekday++) {
        $hours[] = [
            'weekday' => $weekday,
            'is_open' => true,
            'opens_at' => '11:00',
            'closes_at' => '22:00',
            'last_pickup_at' => '21:00',
        ];
    }

    foreach ($overrides as $index => $values) {
        $hours[$index] = [
            ...$hours[$index],
            ...$values,
        ];
    }

    return ['hours' => $hours];
}

it('allows an admin to update all regular hours', function () {
    RestaurantHour::query()->delete();

    $payload = regularHoursPayload([
        0 => [
            'opens_at' => '10:30',
            'closes_at' => '23:00',
            'last_pickup_at' => '22:00',
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )->assertNoContent();

    expect(RestaurantHour::query()->count())->toBe(7);

    $this->assertDatabaseHas('restaurant_hours', [
        'weekday' => 1,
        'is_open' => true,
        'opens_at' => '10:30:00',
        'closes_at' => '23:00:00',
        'last_pickup_at' => '22:00:00',
    ]);
});

it('allows an admin to close a regular weekday', function () {
    $payload = regularHoursPayload([
        6 => [
            'is_open' => false,
            'opens_at' => null,
            'closes_at' => null,
            'last_pickup_at' => null,
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )->assertNoContent();

    $this->assertDatabaseHas('restaurant_hours', [
        'weekday' => 7,
        'is_open' => false,
        'opens_at' => null,
        'closes_at' => null,
        'last_pickup_at' => null,
    ]);
});

it('requires exactly seven distinct weekdays', function () {
    $payload = regularHoursPayload();

    $payload['hours'][6]['weekday'] = 1;

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('hours.6.weekday');
});

it('rejects a closing time before opening time', function () {
    $payload = regularHoursPayload([
        0 => [
            'opens_at' => '18:00',
            'closes_at' => '12:00',
            'last_pickup_at' => '11:00',
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'hours.0.closes_at',
            'hours.0.last_pickup_at',
        ]);
});

it('rejects a last pickup time at or after closing', function () {
    $payload = regularHoursPayload([
        0 => [
            'opens_at' => '11:00',
            'closes_at' => '22:00',
            'last_pickup_at' => '22:00',
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'hours.0.last_pickup_at',
        );
});

it('rejects an unauthenticated request', function () {
    auth()->logout();

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        regularHoursPayload(),
    )->assertUnauthorized();
});
