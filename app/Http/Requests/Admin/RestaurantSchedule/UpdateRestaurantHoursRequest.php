<?php

namespace App\Http\Requests\Admin\RestaurantSchedule;

use App\Enums\WeekdayEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRestaurantHoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $weekdays = array_map(
            fn (WeekdayEnum $weekday) => $weekday->value,
            WeekdayEnum::cases(),
        );

        return [
            'hours' => [
                'required',
                'array',
                'size:7',
            ],
            'hours.*' => [
                'required',
                'array',
            ],
            'hours.*.weekday' => [
                'required',
                'integer',
                'distinct',
                Rule::in($weekdays),
            ],
            'hours.*.is_open' => [
                'required',
                'boolean',
            ],
            'hours.*.periods' => ['present', 'array', 'max:2'],
            'hours.*.periods.*' => ['required', 'array'],

            'hours.*.periods.*.opens_at' => [
                'required',
                'date_format:H:i',
            ],

            'hours.*.periods.*.closes_at' => [
                'required',
                'date_format:H:i',
            ],

            'hours.*.periods.*.last_pickup_at' => [
                'required',
                'date_format:H:i',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hours = $this->input('hours', []);

                if (! is_array($hours)) {
                    return;
                }

                foreach ($hours as $dayIndex => $day) {
                    if (! is_array($day)) {
                        continue;
                    }

                    $isOpen = filter_var(
                        $day['is_open'] ?? false,
                        FILTER_VALIDATE_BOOLEAN,
                    );

                    $periods = $day['periods'] ?? null;

                    if (! is_array($periods)) {
                        // The rules() method reports missing or invalid periods.
                        continue;
                    }

                    if ($isOpen && count($periods) === 0) {
                        $validator->errors()->add(
                            "hours.$dayIndex.periods",
                            'An open day needs at least one period.',
                        );
                    }

                    if (! $isOpen && count($periods) > 0) {
                        $validator->errors()->add(
                            "hours.$dayIndex.periods",
                            'A closed day cannot have opening periods.',
                        );

                        continue;
                    }

                    $validRanges = [];

                    foreach ($periods as $periodIndex => $period) {
                        if (! is_array($period)) {
                            continue;
                        }

                        $opensAt = $period['opens_at'] ?? null;
                        $closesAt = $period['closes_at'] ?? null;
                        $lastPickupAt = $period['last_pickup_at'] ?? null;

                        // rules() reports missing or malformed times.
                        // Compare times only when all three are valid.
                        if (
                            ! $this->isValidTime($opensAt) ||
                            ! $this->isValidTime($closesAt) ||
                            ! $this->isValidTime($lastPickupAt)
                        ) {
                            continue;
                        }

                        if ($opensAt >= $closesAt) {
                            $validator->errors()->add(
                                "hours.$dayIndex.periods.$periodIndex.closes_at",
                                'Closing must be after opening.',
                            );

                            continue;
                        }

                        if (
                            $lastPickupAt < $opensAt ||
                            $lastPickupAt >= $closesAt
                        ) {
                            $validator->errors()->add(
                                "hours.$dayIndex.periods.$periodIndex.last_pickup_at",
                                'Last pickup must be between opening and closing.',
                            );
                        }

                        $validRanges[] = [
                            'index' => $periodIndex,
                            'opens_at' => $opensAt,
                            'closes_at' => $closesAt,
                        ];
                    }

                    // Sort a copy for validation. We do not change the request.
                    usort(
                        $validRanges,
                        fn (array $a, array $b) => strcmp(
                            $a['opens_at'],
                            $b['opens_at'],
                        ),
                    );

                    for ($index = 1; $index < count($validRanges); $index++) {
                        $previous = $validRanges[$index - 1];
                        $current = $validRanges[$index];

                        if ($current['opens_at'] < $previous['closes_at']) {
                            $validator->errors()->add(
                                "hours.$dayIndex.periods.{$current['index']}.opens_at",
                                'Opening periods cannot overlap.',
                            );
                        }
                    }
                }
            },
        ];
    }

    private function isValidTime(mixed $value): bool
    {
        return is_string($value)
            && preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
                $value,
            ) === 1;
    }
}
