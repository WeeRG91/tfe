<?php

use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use App\Services\RestaurantAvailabilityService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config()->set('restaurant.timezone', 'Europe/Luxembourg');

    foreach (range(1, 7) as $weekday) {
        $day = RestaurantHour::query()->updateOrCreate(
            ['weekday' => $weekday],
            [
                'is_open' => true,
            ],
        );

        $day->periods()->create([
            'position' => 1,
            'opens_at' => '11:00:00',
            'closes_at' => '22:00:00',
            'last_pickup_at' => '21:00:00',
        ]);
    }

    $this->availability = app(RestaurantAvailabilityService::class);
});

it('returns todays opening when checked before opening', function () {
    $from = CarbonImmutable::parse(
        '2026-09-14 09:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-14T11:00:00+02:00');
});

it('returns tomorrows opening at the exact closing time', function () {
    $from = CarbonImmutable::parse(
        '2026-09-14 22:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-15T11:00:00+02:00');
});

it('returns reopening after a partial closure', function () {
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
    ]);

    $from = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-14T18:00:00+02:00');
});

it('reopens on 20 september after a multi-day closure', function () {
    RestaurantClosure::query()->create([
        'starts_at' => CarbonImmutable::parse(
            '2026-09-14 00:00:00',
            'Europe/Luxembourg',
        )->utc(),
        'ends_at' => CarbonImmutable::parse(
            '2026-09-20 00:00:00',
            'Europe/Luxembourg',
        )->utc(),
        'is_all_day' => true,
    ]);

    $from = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-20T11:00:00+02:00');
});

it('skips a disabled regular weekday', function () {
    RestaurantHour::query()
        ->where('weekday', 2)
        ->update(['is_open' => false]);

    $from = CarbonImmutable::parse(
        '2026-09-14 22:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-16T11:00:00+02:00');
});

it('does not reopen outside regular opening hours', function () {
    RestaurantClosure::query()->create([
        'starts_at' => CarbonImmutable::parse(
            '2026-09-14 15:00:00',
            'Europe/Luxembourg',
        )->utc(),
        'ends_at' => CarbonImmutable::parse(
            '2026-09-14 23:00:00',
            'Europe/Luxembourg',
        )->utc(),
        'is_all_day' => false,
    ]);

    $from = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-15T11:00:00+02:00');
});

it('continues past adjacent exceptional closures', function () {
    foreach ([
        ['15:00:00', '18:00:00'],
        ['18:00:00', '20:00:00'],
    ] as [$start, $end]) {
        RestaurantClosure::query()->create([
            'starts_at' => CarbonImmutable::parse(
                "2026-09-14 {$start}",
                'Europe/Luxembourg',
            )->utc(),
            'ends_at' => CarbonImmutable::parse(
                "2026-09-14 {$end}",
                'Europe/Luxembourg',
            )->utc(),
            'is_all_day' => false,
        ]);
    }

    $from = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-14T20:00:00+02:00');
});

it('returns null when no weekday is open', function () {
    RestaurantHour::query()->update(['is_open' => false]);

    $from = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from),
    )->toBeNull();
});

it('returns the second opening after the afternoon break', function () {
    $tuesday = RestaurantHour::query()
        ->where('weekday', 2)
        ->firstOrFail();

    $tuesday->periods()->delete();

    $tuesday->periods()->createMany([
        [
            'position' => 1,
            'opens_at' => '11:00:00',
            'closes_at' => '14:30:00',
            'last_pickup_at' => '14:00:00',
        ],
        [
            'position' => 2,
            'opens_at' => '18:00:00',
            'closes_at' => '22:00:00',
            'last_pickup_at' => '21:00:00',
        ],
    ]);

    $from = CarbonImmutable::parse(
        '2026-09-15 15:00:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->nextOpenAt($from)?->toIso8601String(),
    )->toBe('2026-09-15T18:00:00+02:00');
});
