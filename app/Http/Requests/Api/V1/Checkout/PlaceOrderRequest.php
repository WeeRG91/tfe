<?php

namespace App\Http\Requests\Api\V1\Checkout;

use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
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
            'cart_id' => [
                'required',
                'integer',
                Rule::exists('carts', 'id')->where(
                    fn (Builder $query) => $query->where(
                        'user_id',
                        $this->user()->id,
                    ),
                ),
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(OrderTypeEnum::values()),
            ],
            'table_number' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,' . OrderTypeEnum::DINE_IN->value,
                'prohibited_unless:type,' . OrderTypeEnum::DINE_IN->value,
            ],
            'pickup_time' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value,
            ],
            'pickup_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value,
            ],
            'pickup_phone' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,' . OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,' . OrderTypeEnum::TAKEAWAY->value,
            ],
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(
                    fn (Builder $query) => $query->where(
                        'user_id',
                        $this->user()->id,
                    ),
                ),
                'required_if:type,' . OrderTypeEnum::DELIVERY->value,
                'prohibited_unless:type,' . OrderTypeEnum::DELIVERY->value,
            ],
            'payment_method' => [
                'required',
                'integer',
                Rule::in(PaymentMethodEnum::values()),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'used_points' => [
                'sometimes',
                'integer',
                Rule::in([0, 300, 550]),
            ],
        ];
    }
}
