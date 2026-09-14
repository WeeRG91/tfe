<?php

namespace App\Http\Requests\Admin\RestaurantSchedule;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRestaurantClosureRequest extends FormRequest
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
        $isAllDay = $this->boolean('is_all_day');

        return [
            'is_all_day' => [
                'required',
                'boolean',
            ],
            'starts_on' => [
                Rule::requiredIf($isAllDay),
                'nullable',
                'date_format:Y-m-d',
            ],
            'ends_on' => [
                Rule::requiredIf($isAllDay),
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:starts_on',
            ],
            'starts_at' => [
                Rule::requiredIf(! $isAllDay),
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],
            'ends_at' => [
                Rule::requiredIf(! $isAllDay),
                'nullable',
                'date_format:Y-m-d\TH:i',
                'after:starts_at',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],
            'public_message' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}
