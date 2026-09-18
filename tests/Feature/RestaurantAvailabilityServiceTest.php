<?php

use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use App\Services\RestaurantAvailabilityService;
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

    $this->availability = app(RestaurantAvailabilityService::class);
});

it('is closed before the regular opening time', function () {
    $dateTime = CarbonImmutable::parse(
        '2026-09-14 10:59:59',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($dateTime))->toBeFalse();
});

it('is open at the exact regular opening time', function () {
    $dateTime = CarbonImmutable::parse(
        '2026-09-14 11:00:00',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($dateTime))->toBeTrue();
});

it('is open during regular opening hours', function () {
    $dateTime = CarbonImmutable::parse(
        '2026-09-14 16:30:00',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($dateTime))->toBeTrue();
});

it('is closed at the exact regular closing time', function () {
    $dateTime = CarbonImmutable::parse(
        '2026-09-14 22:00:00',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($dateTime))->toBeFalse();
});

it('is closed when the regular weekday is disabled', function () {
    RestaurantHour::query()
        ->where('weekday', 1)
        ->update(['is_open' => false]);

    $dateTime = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    $status = $this->availability->statusAt($dateTime);

    expect($status)
        ->is_open->toBeFalse()
        ->status->toBe('closed_day');
});

it('is closed during an exceptional partial closure', function () {
    $closureStart = CarbonImmutable::parse(
        '2026-09-14 15:00:00',
        'Europe/Luxembourg',
    );

    $closureEnd = CarbonImmutable::parse(
        '2026-09-14 18:00:00',
        'Europe/Luxembourg',
    );

    RestaurantClosure::query()->create([
        'starts_at' => $closureStart->utc(),
        'ends_at' => $closureEnd->utc(),
        'is_all_day' => false,
        'reason' => 'Private event',
        'public_message' => 'Closed temporarily for a private event.',
    ]);

    $beforeClosure = CarbonImmutable::parse(
        '2026-09-14 14:59:59',
        'Europe/Luxembourg',
    );

    $duringClosure = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    $afterClosure = CarbonImmutable::parse(
        '2026-09-14 18:00:00',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($beforeClosure))->toBeTrue()
        ->and($this->availability->isOpenAt($duringClosure))->toBeFalse()
        ->and($this->availability->isOpenAt($afterClosure))->toBeTrue();
});

it('returns the public message for an exceptional closure', function () {
    $closureStart = CarbonImmutable::parse(
        '2026-09-14 15:00:00',
        'Europe/Luxembourg',
    );

    $closureEnd = CarbonImmutable::parse(
        '2026-09-14 18:00:00',
        'Europe/Luxembourg',
    );

    RestaurantClosure::query()->create([
        'starts_at' => $closureStart->utc(),
        'ends_at' => $closureEnd->utc(),
        'is_all_day' => false,
        'reason' => 'Maintenance',
        'public_message' => 'We are temporarily closed for maintenance.',
    ]);

    $dateTime = CarbonImmutable::parse(
        '2026-09-14 16:00:00',
        'Europe/Luxembourg',
    );

    $status = $this->availability->statusAt($dateTime);

    expect($status)
        ->is_open->toBeFalse()
        ->status->toBe('exceptionally_closed')
        ->message->toBe(
            'We are temporarily closed for maintenance.',
        );
});

it('is closed for an exceptional full-day closure', function () {
    $localDate = CarbonImmutable::parse(
        '2026-09-14 00:00:00',
        'Europe/Luxembourg',
    );

    RestaurantClosure::query()->create([
        'starts_at' => $localDate->utc(),
        'ends_at' => $localDate->addDay()->utc(),
        'is_all_day' => true,
        'reason' => 'Public holiday',
        'public_message' => 'We are closed today.',
    ]);

    $lunchTime = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    expect($this->availability->isOpenAt($lunchTime))->toBeFalse();
});

it('generates pickup slots from opening time through last pickup time', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 10:00:00',
        'Europe/Luxembourg',
    );

    $availability = $this->availability->pickupAvailability($now, 1);

    $slots = $availability['dates'][0]['slots'];

    expect($availability['dates'][0]['available'])->toBeTrue()
        ->and($slots[0]['label'])->toBe('11:00')
        ->and($slots[array_key_last($slots)]['label'])->toBe('21:00');
});

it('generates pickup slots for two periods without slots in the break', function () {
    $monday = RestaurantHour::query()
        ->where('weekday', 1)
        ->firstOrFail();

    // Replace the default all-day period for this test only.
    $monday->periods()->delete();

    $monday->periods()->createMany([
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

    $now = CarbonImmutable::parse(
        '2026-09-14 10:00:00',
        'Europe/Luxembourg',
    );

    $availability = $this->availability->pickupAvailability($now, 1);
    $date = $availability['dates'][0];
    $labels = collect($date['slots'])->pluck('label');

    expect($date['available'])->toBeTrue()
        ->and($date['periods'])->toHaveCount(2)
        ->and($labels)
        ->toContain('11:00')
        ->toContain('14:00')
        ->not->toContain('14:15')
        ->not->toContain('14:30')
        ->not->toContain('17:45')
        ->toContain('18:00')
        ->toContain('21:00')
        ->not->toContain('21:15');

});

it('rounds the minimum pickup time up to the next interval', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 11:07:00',
        'Europe/Luxembourg',
    );

    /*
     * 11:07 + 30 minutes = 11:37
     *     *     * Rounded upward to the next 15-minute slot = 11:45
     */
    $availability = $this->availability->pickupAvailability($now, 1);

    $slots = $availability['dates'][0]['slots'];

    expect($slots[0]['label'])->toBe('11:45');
});

it('returns no remaining slots when preparation time passes the last pickup', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 20:40:00',
        'Europe/Luxembourg',
    );

    /*
     * 20:40 + 30 minutes = 21:10
     * Rounded upward = 21:15
     * Last pickup = 21:00
     */
    $availability = $this->availability->pickupAvailability($now, 1);

    expect($availability['dates'][0]['available'])->toBeFalse()
        ->and($availability['dates'][0]['slots'])->toBeEmpty();
});

it('removes slots covered by an exceptional closure', function () {
    $closureStart = CarbonImmutable::parse(
        '2026-09-14 15:00:00',
        'Europe/Luxembourg',
    );

    $closureEnd = CarbonImmutable::parse(
        '2026-09-14 18:00:00',
        'Europe/Luxembourg',
    );

    RestaurantClosure::query()->create([
        'starts_at' => $closureStart->utc(),
        'ends_at' => $closureEnd->utc(),
        'is_all_day' => false,
        'reason' => 'Private event',
    ]);

    $now = CarbonImmutable::parse(
        '2026-09-14 10:00:00',
        'Europe/Luxembourg',
    );

    $availability = $this->availability->pickupAvailability($now, 1);

    $labels = collect($availability['dates'][0]['slots'])
        ->pluck('label');

    expect($labels)
        ->toContain('14:45')
        ->not->toContain('15:00')
        ->not->toContain('16:00')
        ->not->toContain('17:45')
        ->toContain('18:00');
});

it('returns no pickup slots during a full-day closure', function () {
    $localDate = CarbonImmutable::parse(
        '2026-09-14 00:00:00',
        'Europe/Luxembourg',
    );

    RestaurantClosure::query()->create([
        'starts_at' => $localDate->utc(),
        'ends_at' => $localDate->addDay()->utc(),
        'is_all_day' => true,
        'reason' => 'Public holiday',
    ]);

    $now = CarbonImmutable::parse(
        '2026-09-14 10:00:00',
        'Europe/Luxembourg',
    );

    $availability = $this->availability->pickupAvailability($now, 1);

    expect($availability['dates'][0]['available'])->toBeFalse()
        ->and($availability['dates'][0]['slots'])->toBeEmpty();
});

it('validates a pickup time against the generated slots', function () {
    $now = CarbonImmutable::parse(
        '2026-09-14 11:00:00',
        'Europe/Luxembourg',
    );

    $validPickup = CarbonImmutable::parse(
        '2026-09-14 12:00:00',
        'Europe/Luxembourg',
    );

    $invalidInterval = CarbonImmutable::parse(
        '2026-09-14 12:05:00',
        'Europe/Luxembourg',
    );

    $afterLastPickup = CarbonImmutable::parse(
        '2026-09-14 21:15:00',
        'Europe/Luxembourg',
    );

    expect(
        $this->availability->isValidPickupSlot($validPickup, $now),
    )->toBeTrue()
        ->and(
            $this->availability->isValidPickupSlot(
                $invalidInterval,
                $now,
            ),
        )->toBeFalse()
        ->and(
            $this->availability->isValidPickupSlot(
                $afterLastPickup,
                $now,
            ),
        )->toBeFalse();
});

it('is closed between two Tuesday opening periods', function () {
    $tuesday = RestaurantHour::query()->create([
        'weekday' => 2,
        'is_open' => true,
    ]);

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

    foreach ([
        '11:00:00' => true,
        '14:29:59' => true,
        '14:30:00' => false,
        '17:59:59' => false,
        '18:00:00' => true,
        '22:00:00' => false,
    ] as $time => $expected) {
        $dateTime = CarbonImmutable::parse(
            "2026-09-15 {$time}",
            'Europe/Luxembourg',
        );

        expect($this->availability->isOpenAt($dateTime))
            ->toBe($expected);
    }
});
