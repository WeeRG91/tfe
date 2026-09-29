<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        $timezone = config('restaurant.timezone');
        $now = CarbonImmutable::now($timezone);

        $hours = RestaurantHour::query()
            ->with('periods')
            ->orderBy('weekday')
            ->get()
            ->map(fn (RestaurantHour $hour) => [
                'id' => $hour->id,
                'weekday' => $hour->weekday->value,
                'weekday_key' => $hour->weekday->key(),
                'is_open' => $hour->is_open,
                'periods' => $hour->periods
                    ->map(fn ($period) => [
                        'id' => $period->id,
                        'position' => $period->position,
                        'opens_at' => $this->shortTime($period->opens_at),
                        'closes_at' => $this->shortTime($period->closes_at),
                    ])
                    ->values(),
            ])
            ->values();

        $closures = RestaurantClosure::query()
            ->where('ends_at', '>', $now->utc())
            ->orderBy('starts_at')
            ->get()
            ->map(function (RestaurantClosure $closure) use ($timezone) {
                $startsAt = $closure->starts_at
                    ->toImmutable()
                    ->setTimezone($timezone);

                $endsAt = $closure->ends_at
                    ->toImmutable()
                    ->setTimezone($timezone);

                return [
                    'id' => $closure->id,
                    'is_all_day' => $closure->is_all_day,
                    'starts_at' => $startsAt->toIso8601String(),
                    'ends_at' => $endsAt->toIso8601String(),
                    'public_message' => $closure->public_message,
                ];
            })
            ->values();

        return Inertia::render('client/About', [
            'hours' => $hours,
            'closures' => $closures,
            'timezone' => $timezone,
        ]);
    }

    private function shortTime(?string $time): ?string
    {
        return $time === null
            ? null
            : substr($time, 0, 5);
    }
}
