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
            'hours.*.opens_at' => [
                'nullable',
                'date_format:H:i',
            ],
            'hours.*.closes_at' => [
                'nullable',
                'date_format:H:i',
            ],
            'hours.*.last_pickup_at' => [
                'nullable',
                'date_format:H:i',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hours = $this->input('hours', []);

                foreach ($hours as $index => $day) {
                    if (! is_array($day)) {
                        continue;
                    }

                    $isOpen = filter_var(
                        $day['is_open'] ?? false,
                        FILTER_VALIDATE_BOOLEAN,
                    );

                    if (! $isOpen) {
                        continue;
                    }

                    $opensAt = $day['opens_at'] ?? null;
                    $closesAt = $day['closes_at'] ?? null;
                    $lastPickupAt = $day['last_pickup_at'] ?? null;

                    if ($opensAt === null) {
                        $validator->errors()->add(
                            "hours.$index.opens_at",
                            'The opening time is required for an open day.',
                        );
                    }

                    if ($closesAt === null) {
                        $validator->errors()->add(
                            "hours.$index.closes_at",
                            'The closing time is required for an open day.',
                        );
                    }

                    if ($lastPickupAt === null) {
                        $validator->errors()->add(
                            "hours.$index.last_pickup_at",
                            'The last pickup time is required for an open day.',
                        );
                    }

                    if (
                        $opensAt === null ||
                        $closesAt === null ||
                        $lastPickupAt === null ||
                        ! $this->isValidTime($opensAt) ||
                        ! $this->isValidTime($closesAt) ||
                        ! $this->isValidTime($lastPickupAt)
                    ) {
                        continue;
                    }

                    if ($opensAt >= $closesAt) {
                        $validator->errors()->add(
                            "hours.$index.closes_at",
                            'The closing time must be after the opening time.',
                        );
                    }

                    if ($lastPickupAt < $opensAt) {
                        $validator->errors()->add(
                            "hours.$index.last_pickup_at",
                            'The last pickup time cannot be before opening.',
                        );
                    }

                    if ($lastPickupAt >= $closesAt) {
                        $validator->errors()->add(
                            "hours.$index.last_pickup_at",
                            'The last pickup time must be before closing.',
                        );
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
