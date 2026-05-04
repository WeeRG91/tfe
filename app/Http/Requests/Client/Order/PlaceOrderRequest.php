<?php

namespace App\Http\Requests\Client\Order;

use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
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
            'cart_id' => ['required', 'exists:carts,id'],
            'type' => ['required', Rule::in(OrderTypeEnum::values())],
            'table_number' => [
                'nullable',
                'string',
                'required_if:type,' . OrderTypeEnum::DINE_IN->value,
                'prohibited_unless:type,' . OrderTypeEnum::DINE_IN->value
            ],
            'pickup_time' => [
                'nullable',
                'date',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value
            ],
            'pickup_name' => [
                'nullable',
                'string',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value
            ],
            'pickup_phone' => [
                'nullable',
                'string',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value
            ],
            'address_id' => [
                'nullable',
                'exists:addresses,id',
                'required_if:type,' . OrderTypeEnum::DELIVERY->value,
                'prohibited_unless:type,' . OrderTypeEnum::DELIVERY->value
            ],
            'payment_method' => ['required', Rule::in(PaymentMethodEnum::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
