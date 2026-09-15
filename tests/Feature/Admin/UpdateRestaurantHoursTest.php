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
            'periods' => [
                [
                    'opens_at' => '11:00',
                    'closes_at' => '22:00',
                    'last_pickup_at' => '21:00',
                ],
            ],
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

function splitTuesdayPeriods(): array
{
    return [
        [
            'opens_at' => '11:00',
            'closes_at' => '14:30',
            'last_pickup_at' => '14:00',
        ],
        [
            'opens_at' => '18:00',
            'closes_at' => '22:00',
            'last_pickup_at' => '21:00',
        ],
    ];
}

it('saves one or two opening periods per weekday', function () {
    $payload = regularHoursPayload([
        1 => ['periods' => splitTuesdayPeriods()],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )->assertNoContent();

    expect(RestaurantHour::query()->count())->toBe(7);

    $tuesday = RestaurantHour::query()
        ->where('weekday', 2)
        ->firstOrFail();

    expect($tuesday->is_open)->toBeTrue()
        ->and($tuesday->periods()->count())->toBe(2);

    $this->assertDatabaseHas('restaurant_hour_periods', [
        'restaurant_hour_id' => $tuesday->id,
        'position' => 1,
        'opens_at' => '11:00:00',
        'closes_at' => '14:30:00',
        'last_pickup_at' => '14:00:00',
    ]);

    $this->assertDatabaseHas('restaurant_hour_periods', [
        'restaurant_hour_id' => $tuesday->id,
        'position' => 2,
        'opens_at' => '18:00:00',
        'closes_at' => '22:00:00',
        'last_pickup_at' => '21:00:00',
    ]);
});

it('removes the second period when a day returns to one period', function () {
    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        regularHoursPayload([
            1 => ['periods' => splitTuesdayPeriods()],
        ]),
    )->assertNoContent();

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        regularHoursPayload(),
    )->assertNoContent();

    $tuesday = RestaurantHour::query()
        ->where('weekday', 2)
        ->firstOrFail();

    expect($tuesday->periods()->count())->toBe(1);

    $this->assertDatabaseMissing('restaurant_hour_periods', [
        'restaurant_hour_id' => $tuesday->id,
        'position' => 2,
    ]);
});

it('allows an admin to close a regular weekday', function () {
    $payload = regularHoursPayload([
        6 => [
            'is_open' => false,
            'periods' => [],
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )->assertNoContent();

    $sunday = RestaurantHour::query()
        ->where('weekday', 7)
        ->firstOrFail();

    expect($sunday->is_open)->toBeFalse()
        ->and($sunday->periods()->count())->toBe(0);
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

it('requires at least one period for an open weekday', function () {
    $payload = regularHoursPayload([
        0 => ['periods' => []],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('hours.0.periods');
});

it('rejects periods on a closed weekday', function () {
    $payload = regularHoursPayload([
        0 => ['is_open' => false],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('hours.0.periods');
});

it('allows no more than two periods per weekday', function () {
    $payload = regularHoursPayload([
        0 => [
            'periods' => [
                [
                    'opens_at' => '11:00',
                    'closes_at' => '14:00',
                    'last_pickup_at' => '13:30',
                ],
                [
                    'opens_at' => '16:00',
                    'closes_at' => '19:00',
                    'last_pickup_at' => '18:30',
                ],
                [
                    'opens_at' => '20:00',
                    'closes_at' => '23:00',
                    'last_pickup_at' => '22:30',
                ],
            ],
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('hours.0.periods');
});

it('rejects a closing time before opening time', function () {
    $payload = regularHoursPayload([
        0 => [
            'periods' => [
                [
                    'opens_at' => '18:00',
                    'closes_at' => '12:00',
                    'last_pickup_at' => '11:00',
                ],
            ],
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'hours.0.periods.0.closes_at',
        );
});

it('rejects a last pickup time at or after closing', function () {
    $payload = regularHoursPayload([
        0 => [
            'periods' => [
                [
                    'opens_at' => '11:00',
                    'closes_at' => '22:00',
                    'last_pickup_at' => '22:00',
                ],
            ],
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'hours.0.periods.0.last_pickup_at',
        );
});

it('rejects overlapping opening periods', function () {
    $payload = regularHoursPayload([
        0 => [
            'periods' => [
                [
                    'opens_at' => '11:00',
                    'closes_at' => '14:30',
                    'last_pickup_at' => '14:00',
                ],
                [
                    'opens_at' => '13:00',
                    'closes_at' => '22:00',
                    'last_pickup_at' => '21:00',
                ],
            ],
        ],
    ]);

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        $payload,
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'hours.0.periods.1.opens_at',
        );
});

it('rejects an unauthenticated request', function () {
    auth()->logout();

    $this->putJson(
        route('admin.restaurant-schedule.hours.update'),
        regularHoursPayload(),
    )->assertUnauthorized();
});
