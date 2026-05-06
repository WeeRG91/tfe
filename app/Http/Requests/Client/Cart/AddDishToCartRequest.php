<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'item_id' => 'required',
            'item_type' => 'required|in:dish,drink',
            'meat_id' => 'required|exists:meats,id',
            'quantity' => 'required|integer|min:1',
            'removed_ingredients' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
