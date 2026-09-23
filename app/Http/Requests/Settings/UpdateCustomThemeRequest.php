<?php

namespace App\Http\Requests\Settings;

use App\Enums\Permissions\AdminPermissionEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(
            AdminPermissionEnum::THEME_MANAGE->value,
        ) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'colors' => ['required', 'array'],
            'colors.*' => [
                'required',
                'string',
                'regex:/\A#[0-9A-Fa-f]{6}\z/',
            ],
        ];
    }
}
