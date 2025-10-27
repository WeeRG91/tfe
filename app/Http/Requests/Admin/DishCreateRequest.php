<?php

namespace App\Http\Requests\Admin;

use App\Enums\DishCategoryEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class DishCreateRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', new Enum(DishCategoryEnum::class)],
            'price' => ['required', 'numeric'],
            'ingredients' => ['required', 'array', 'exists:ingredients,id'],
            'images' => ['nullable', 'array'],
        ];
    }
}
