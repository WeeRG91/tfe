<?php

namespace App\Services;

use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class RestaurantAvailabilityService
{
    public function isOpenAt(CarbonInterface $dateTime): bool
    {
        $localDateTime = $this->toRestaurantTimezone($dateTime);

        $schedule = $this->scheduleFor($localDateTime);
        if ($schedule === null || ! $schedule->is_open) {
            return false;
        }

        if (! $this->isInsideRegularHours($localDateTime, $schedule)) {
            return false;
        }

        return $this->closureAt($localDateTime) === null;
    }

    public function statusAt(CarbonInterface $dateTime): array
    {
        $localDateTime = $this->toRestaurantTimezone($dateTime);

        $schedule = $this->scheduleFor($localDateTime);

        if ($schedule === null || ! $schedule->is_open) {
            return [
                'is_open' => false,
                'status' => 'closed_day',
                'message' => null,
            ];
        }

        if (! $this->isInsideRegularHours($localDateTime, $schedule)) {
            return [
                'is_open' => false,
                'status' => 'outside_opening_hours',
                'message' => null,
            ];
        }

        $closure = $this->closureAt($localDateTime);

        if ($closure !== null) {
            return [
                'is_open' => false,
                'status' => 'exceptionally_closed',
                'message' => $closure->public_message,
            ];
        }

        return [
            'is_open' => true,
            'status' => 'open',
            'message' => null,
        ];
    }

    public function orderingStatusAt(
        ?CarbonInterface $dateTime = null,
    ): array {
        $dateTime ??= CarbonImmutable::now(
            config('restaurant.timezone'),
        );

        $status = $this->statusAt($dateTime);

        return [
            ...$status,
            'accepting_orders' => (bool) config(
                'restaurant.allow_orders_while_closed',
                false,
            ) || $status['is_open'],
        ];
    }

    public function assertCanAcceptOrders(
        ?CarbonInterface $dateTime = null,
    ): void {
        $status = $this->orderingStatusAt($dateTime);

        if ($status['accepting_orders']) {
            return;
        }

        throw ValidationException::withMessages([
            'restaurant' => [
                $status['message']
                    ?: __('messages.restaurant.closed'),
            ],
        ]);
    }

    public function nextOpenAt(
        ?CarbonInterface $from = null,
        ?int $days = null,
    ): ?CarbonImmutable
    {
        $localFrom = $this->toRestaurantTimezone(
            $from ?? CarbonImmutable::now(
                config('restaurant.timezone'),
            ),
        );

        $days ??= (int) config(
            'restaurant.next_open_search_days',
            90,
        );

        if ($days <= 0) {
            throw new InvalidArgumentException(
                'The next-opening search range must be greater than zero.',
            );
        }

        $firstDate = $localFrom->startOfDay();

        $schedules = RestaurantHour::query()
            ->with('periods')
            ->get()
            ->keyBy(
                fn (RestaurantHour $hour) => $hour->weekday->value,
            );

        $closures = RestaurantClosure::query()
            ->where('starts_at', '<', $firstDate->addDays($days)->utc())
            ->where('ends_at', '>', $localFrom->utc())
            ->orderBy('starts_at')
            ->get();

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $firstDate->addDays($offset);

            $schedule = $schedules->get($date->dayOfWeekIso);

            if (
                $schedule === null ||
                !$schedule->is_open
            ) {
                continue;
            }

            foreach ($schedule->periods as $period) {
                $opening = $date->setTimeFromTimeString(
                    $period->opens_at,
                );

                $closing = $date->setTimeFromTimeString(
                    $period->closes_at,
                );

                if (
                    $opening->greaterThanOrEqualTo($closing) ||
                    $closing->lessThanOrEqualTo($localFrom)
                ) {
                    continue;
                }

                $candidate = $opening->lessThan($localFrom)
                    ? $localFrom
                    : $opening;

                foreach ($closures as $closure) {
                    $closureStart = $this->toRestaurantTimezone(
                        $closure->starts_at,
                    );

                    $closureEnd = $this->toRestaurantTimezone(
                        $closure->ends_at,
                    );

                    if ($closureEnd->lessThanOrEqualTo($candidate)) {
                        continue;
                    }

                    if ($closureStart->greaterThan($candidate)) {
                        break;
                    }

                    $candidate = $closureEnd;

                    if ($candidate->greaterThanOrEqualTo($closing)) {
                        break;
                    }
                }

                if ($candidate->lessThan($closing)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function pickupAvailability(
        ?CarbonInterface $from = null,
        ?int $days = null,
    ): array {
        $localNow = $this->toRestaurantTimezone(
            $from ?? CarbonImmutable::now(config('restaurant.timezone')),
        );

        $days = max(
            1,
            $days ?? config('restaurant.pickup.availability_days'),
        );

        $slotInterval = config('restaurant.pickup.slot_interval_minutes');

        $minimumPreparation = config('restaurant.pickup.minimum_preparation_minutes');

        $minimumPickupTime = $this->roundUpToInterval(
            $localNow->addMinutes($minimumPreparation),
            $slotInterval,
        );

        $schedules = RestaurantHour::query()
            ->with('periods')
            ->get()
            ->keyBy(fn (RestaurantHour $hour) => $hour->weekday->value);

        $rangeStart = $localNow
            ->startOfDay()
            ->utc();

        $rangeEnd = $localNow
            ->startOfDay()
            ->addDays($days)
            ->utc();

        $closures = RestaurantClosure::query()
            ->where('starts_at', '<', $rangeEnd)
            ->where('ends_at', '>', $rangeStart)
            ->orderBy('starts_at')
            ->get();

        $availableDates = [];

        for ($dayOffset = 0; $dayOffset < $days; $dayOffset++) {
            $date = $localNow
                ->startOfDay()
                ->addDays($dayOffset);

            $schedule = $schedules->get($date->dayOfWeekIso);

            $slots = [];
            $periods = [];

            if (
                $schedule !== null &&
                $schedule->is_open
            ) {
                foreach ($schedule->periods as $period) {
                    $periods[] = [
                        'position' => $period->position,
                        'opens_at' => $period->opens_at,
                        'closes_at' => $period->closes_at,
                        'last_pickup_at' => $period->last_pickup_at,
                    ];

                    $openingDateTime = $date->setTimeFromTimeString(
                        $period->opens_at,
                    );

                    $closingDateTime = $date->setTimeFromTimeString(
                        $period->closes_at,
                    );

                    $lastPickupDateTime = $date->setTimeFromTimeString(
                        $period->last_pickup_at,
                    );

                    for (
                        $slot = $openingDateTime;
                        $slot->lessThanOrEqualTo($lastPickupDateTime);
                        $slot = $slot->addMinutes($slotInterval)
                    ) {
                        if ($slot->lessThan($minimumPickupTime)) {
                            continue;
                        }

                        if ($slot->greaterThanOrEqualTo($closingDateTime)) {
                            continue;
                        }

                        if ($this->isCoveredByClosure($slot, $closures)) {
                            continue;
                        }

                        $slots[] = [
                            'value' => $slot->toIso8601String(),
                            'label' => $slot->format('H:i'),
                        ];
                    }
                }
            }

            $availableDates[] = [
                'date' => $date->format('Y-m-d'),
                'weekday' => $date->dayOfWeekIso,
                'available' => count($slots) > 0,
                'periods' => $periods,
                'slots' => $slots,
            ];
        }

        return [
            'generated_at' => $localNow->toIso8601String(),
            'timezone' => config('restaurant.timezone'),
            'slot_interval_minutes' => $slotInterval,
            'minimum_preparation_minutes' => $minimumPreparation,
            'dates' => $availableDates,
        ];
    }

    public function parsePickupTime(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $value,
            config('restaurant.timezone'),
        )->setTimezone(config('restaurant.timezone'));
    }

    public function isValidPickupSlot(
        CarbonInterface $pickupTime,
        ?CarbonInterface $now = null,
    ): bool {
        $pickupTime = $this->toRestaurantTimezone($pickupTime);

        $availability = $this->pickupAvailability($now);

        foreach ($availability['dates'] as $date) {
            foreach ($date['slots'] as $slot) {
                $availableSlot = CarbonImmutable::parse($slot['value']);

                if ($availableSlot->equalTo($pickupTime)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function toRestaurantTimezone(CarbonInterface $dateTime): CarbonImmutable
    {
        return CarbonImmutable::instance($dateTime)
            ->setTimezone(config('restaurant.timezone'));
    }

    private function scheduleFor(CarbonInterface $localDateTime): ?RestaurantHour
    {
        return RestaurantHour::query()
            ->with('periods')
            ->where('weekday', $localDateTime->dayOfWeekIso)
            ->first();
    }

    private function isInsideRegularHours(
        CarbonInterface $localDateTime,
        RestaurantHour $schedule
    ): bool {
        $date = $localDateTime->startOfDay();

        foreach ($schedule->periods as $period) {
            $opening = $date->setTimeFromTimeString(
                $period->opens_at,
            );

            $closing = $date->setTimeFromTimeString(
                $period->closes_at,
            );

            if (
                $localDateTime->greaterThanOrEqualTo($opening) &&
                $localDateTime->lessThan($closing)
            ) {
                return true;
            }
        }

        return false;
    }

    private function roundUpToInterval(
        CarbonInterface $dateTime,
        int $intervalMinutes,
    ): CarbonImmutable {
        if ($intervalMinutes <= 0) {
            throw new InvalidArgumentException(
                'The pickup slot interval must be greater than zero.',
            );
        }

        $intervalSeconds = $intervalMinutes * 60;

        $roundTimestamp = (int) (
            ceil($dateTime->getTimestamp() / $intervalSeconds)
            * $intervalSeconds
        );

        return CarbonImmutable::createFromTimestamp(
            $roundTimestamp,
            $dateTime->getTimezone()
        );
    }

    private function isCoveredByClosure(
        CarbonImmutable $slot,
        iterable $closures,
    ): bool {
        $utcSlot = $slot->utc();

        foreach ($closures as $closure) {
            if (
                $utcSlot->greaterThanOrEqualTo($closure->starts_at) &&
                $utcSlot->lessThan($closure->ends_at)
            ) {
                return true;
            }
        }

        return false;
    }

    private function closureAt(CarbonInterface $localDateTime): ?RestaurantClosure
    {
        $utcDateTime = $localDateTime->utc();

        return RestaurantClosure::query()
            ->where('starts_at', '<=', $utcDateTime)
            ->where('ends_at', '>', $utcDateTime)
            ->orderBy('starts_at')
            ->first();
    }
}
