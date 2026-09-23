<?php

namespace App\Http\Requests\Settings;

use App\Enums\Permissions\AdminPermissionEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantThemeRequest extends FormRequest
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
            'default_client_theme_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^(builtin|custom)\/[a-zA-Z0-9-]+$/',
            ],
        ];
    }
}
