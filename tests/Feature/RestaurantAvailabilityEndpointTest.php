<?php

use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');

    $day = RestaurantHour::query()->create([
        'weekday' => 1,
        'is_open' => true,
    ]);

    $day->periods()->create([
        'position' => 1,
        'opens_at' => '11:00:00',
        'closes_at' => '22:00:00',
        'last_pickup_at' => '21:00:00',
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('returns the current status and pickup availability publicly', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-14 12:00:00',
            'Europe/Luxembourg',
        ),
    );

    $this->getJson(route('restaurant.availability', [
        'days' => 1,
    ]))
        ->assertOk()
        ->assertJsonPath('data.current.is_open', true)
        ->assertJsonPath('data.current.status', 'open')
        ->assertJsonPath('data.current.next_open_at', null)
        ->assertJsonPath(
            'data.current.timezone',
            'Europe/Luxembourg',
        )
        ->assertJsonPath(
            'data.current.checked_at',
            '2026-09-14T12:00:00+02:00',
        )
        ->assertJsonPath(
            'data.pickup.timezone',
            'Europe/Luxembourg',
        )
        ->assertJsonPath(
            'data.pickup.slot_interval_minutes',
            15,
        )
        ->assertJsonPath(
            'data.pickup.minimum_preparation_minutes',
            30,
        )
        ->assertJsonPath(
            'data.pickup.dates.0.date',
            '2026-09-14',
        )
        ->assertJsonPath(
            'data.pickup.dates.0.available',
            true,
        )
        ->assertJsonPath(
            'data.pickup.dates.0.slots.0.label',
            '12:30',
        );
});

it('reports an exceptional closure through the public endpoint', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    CarbonImmutable::setTestNow($now);

    RestaurantClosure::query()->create([
        'starts_at' => CarbonImmutable::parse(
            '2026-09-14 15:00:00',
            'Europe/Luxembourg',
        )->utc(),

        'ends_at' => CarbonImmutable::parse(
            '2026-09-14 18:00:00',
            'Europe/Luxembourg',
        )->utc(),

        'is_all_day' => false,
        'reason' => 'Private event',
        'public_message' => 'Closed temporarily for a private event.',
    ]);

    $this->getJson(route('restaurant.availability', [
        'days' => 1,
    ]))
        ->assertOk()
        ->assertJsonPath('data.current.is_open', false)
        ->assertJsonPath(
            'data.current.status',
            'exceptionally_closed',
        )
        ->assertJsonPath(
            'data.current.message',
            'Closed temporarily for a private event.',
        )
        ->assertJsonPath(
            'data.current.next_open_at',
            '2026-09-14T18:00:00+02:00',
        );
});

it('does not require authentication', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-14 12:00:00',
            'Europe/Luxembourg',
        ),
    );

    expect(auth()->check())->toBeFalse();

    $this->getJson(route('restaurant.availability'))
        ->assertOk();
});

it('rejects an excessive availability range', function () {
    $this->getJson(route('restaurant.availability', [
        'days' => 1000,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('days');
});

it('returns the next opening through the mobile availability endpoint', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse(
            '2026-09-14 09:00:00',
            'Europe/Luxembourg',
        ),
    );

    $this->getJson('/api/v1/restaurant/availability?days=1')
        ->assertOk()
        ->assertJsonPath('data.current.is_open', false)
        ->assertJsonPath(
            'data.current.timezone',
            'Europe/Luxembourg',
        )
        ->assertJsonPath(
            'data.current.next_open_at',
            '2026-09-14T11:00:00+02:00',
        );
});
