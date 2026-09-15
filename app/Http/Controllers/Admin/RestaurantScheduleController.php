<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestaurantSchedule\SaveRestaurantClosureRequest;
use App\Http\Requests\Admin\RestaurantSchedule\UpdateRestaurantHoursRequest;
use App\Models\RestaurantClosure;
use App\Models\RestaurantHour;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class RestaurantScheduleController extends Controller
{
    public function index(): InertiaResponse
    {
        $timezone = config('restaurant.timezone');

        $hours = RestaurantHour::query()
            ->with('periods')
            ->orderBy('weekday')
            ->get()
            ->map(fn (RestaurantHour $hour) => [
                'id' => $hour->id,
                'weekday' => $hour->weekday->value,
                'weekday_key' => $hour->weekday->key(),
                'is_open' => $hour->is_open,
                'periods' => $hour->periods->map(fn ($period) => [
                    'id' => $period->id,
                    'position' => $period->position,
                    'opens_at' => $this->shortTime($period->opens_at),
                    'closes_at' => $this->shortTime($period->closes_at),
                    'last_pickup_at' => $this->shortTime(
                        $period->last_pickup_at,
                    ),
                ])->values(),
            ])
            ->values();

        $closures = RestaurantClosure::query()
            ->with('createdBy:id,name')
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

                    'starts_on' => $closure->is_all_day
                        ? $startsAt->format('Y-m-d')
                        : null,

                    'ends_on' => $closure->is_all_day
                        ? $endsAt->subDay()->format('Y-m-d')
                        : null,

                    'reason' => $closure->reason,
                    'public_message' => $closure->public_message,
                    'created_by' => $closure->createdBy?->name,
                ];
            })
            ->values();

        return Inertia::render('admin/restaurant-schedule/Index', [
            'hours' => $hours,
            'closures' => $closures,
            'timezone' => $timezone,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function updateHours(
        UpdateRestaurantHoursRequest $request,
    ): Response {
        DB::transaction(function () use ($request): void {
            foreach ($request->validated('hours') as $day) {
                $isOpen = (bool) $day['is_open'];
                $periods = $day['periods'];

                usort(
                    $periods,
                    fn (array $a, array $b) => strcmp(
                        $a['opens_at'],
                        $b['opens_at'],
                    ),
                );

                $hour = RestaurantHour::query()->updateOrCreate(
                    [
                        'weekday' => $day['weekday'],
                    ],
                    [
                        'is_open' => $isOpen,
                    ]
                );

                $hour->periods()->delete();

                foreach ($periods as $index => $period) {
                    $hour->periods()->create([
                        'position' => $index + 1,
                        'opens_at' => $this->normalizeTime(
                            $period['opens_at'],
                        ),
                        'closes_at' => $this->normalizeTime(
                            $period['closes_at'],
                        ),
                        'last_pickup_at' => $this->normalizeTime(
                            $period['last_pickup_at'],
                        ),
                    ]);
                }
            }
        });

        return response()->noContent();
    }

    public function storeClosure(
        SaveRestaurantClosureRequest $request,
    ): JsonResponse {
        $data = $request->validated();

        [$startsAt, $endsAt] = $this->closurePeriod($data);

        $this->ensureClosureDoesNotOverlap(
            startsAt: $startsAt,
            endsAt: $endsAt,
            errorField: $data['is_all_day']
                ? 'starts_on'
                : 'starts_at',
        );

        $closure = RestaurantClosure::query()->create([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_all_day' => $data['is_all_day'],
            'reason' => $data['reason'] ?? null,
            'public_message' => $data['public_message'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'data' => [
                'id' => $closure->id,
            ],
        ], 201);
    }

    public function updateClosure(
        SaveRestaurantClosureRequest $request,
        RestaurantClosure $restaurantClosure,
    ): Response {
        $data = $request->validated();

        [$startsAt, $endsAt] = $this->closurePeriod($data);

        $this->ensureClosureDoesNotOverlap(
            startsAt: $startsAt,
            endsAt: $endsAt,
            except: $restaurantClosure,
            errorField: $data['is_all_day']
                ? 'starts_on'
                : 'starts_at',
        );

        $restaurantClosure->update([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_all_day' => $data['is_all_day'],
            'reason' => $data['reason'] ?? null,
            'public_message' => $data['public_message'] ?? null,
        ]);

        return response()->noContent();
    }

    public function destroyClosure(RestaurantClosure $restaurantClosure): Response
    {
        $restaurantClosure->delete();

        return response()->noContent();
    }

    private function closurePeriod(array $data): array
    {
        $timezone = config('restaurant.timezone');

        if ($data['is_all_day']) {
            $startsAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                "{$data['starts_on']} 00:00",
                $timezone,
            );

            $endsAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                "{$data['ends_on']} 00:00",
                $timezone,
            )->addDay();
        } else {
            $startsAt = CarbonImmutable::createFromFormat(
                'Y-m-d\TH:i',
                $data['starts_at'],
                $timezone,
            );

            $endsAt = CarbonImmutable::createFromFormat(
                'Y-m-d\TH:i',
                $data['ends_at'],
                $timezone,
            );
        }

        return [
            $startsAt->utc(),
            $endsAt->utc(),
        ];
    }

    private function ensureClosureDoesNotOverlap(
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?RestaurantClosure $except = null,
        string $errorField = 'starts_at',
    ): void {
        $query = RestaurantClosure::query()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);

        if ($except !== null) {
            $query->whereKeyNot($except->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                $errorField => [
                    'This closure overlaps an existing closure.',
                ],
            ]);
        }
    }

    private function normalizeTime(string $time): string
    {
        return "{$time}:00";
    }

    private function shortTime(?string $time): ?string
    {
        return $time === null
            ? null
            : substr($time, 0, 5);
    }
}
