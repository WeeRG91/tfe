<?php

namespace App\Http\Requests\Client\Cart;

use App\Enums\ItemTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AddDishToCartRequest extends FormRequest
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
            'item_id' => ['required'],
            'item_type' => ['required', new Enum(ItemTypeEnum::class)],
            'meat_id' => [
                'nullable',
                'required_if:item_type,' . ItemTypeEnum::DISH->value,
                'exists:meats,id'
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'spicy_level' => ['required', 'integer', 'between:0,3'],
            'removed_ingredients' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
