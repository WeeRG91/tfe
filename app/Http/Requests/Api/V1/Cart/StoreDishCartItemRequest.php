<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDishCartItemRequest extends FormRequest
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
        $dishId = $this->integer('dish_id');

        return [
            'dish_id' => [
                'required',
                'integer',
                Rule::exists('dishes', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_available', true),
            ],
            'meat_id' => [
                'required',
                'integer',
                Rule::exists('meats', 'id')
                    ->whereNull('deleted_at'),
                Rule::exists('dish_meats', 'meat_id')
                    ->where('dish_id', $dishId),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:50',
            ],
            'spicy_level' => [
                'required',
                'integer',
                'between:0,3',
            ],
            'removed_ingredient_ids' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'removed_ingredient_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('ingredients', 'id')
                    ->whereNull('deleted_at'),
                Rule::exists('dish_ingredients', 'ingredient_id')
                    ->where('dish_id', $dishId),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
