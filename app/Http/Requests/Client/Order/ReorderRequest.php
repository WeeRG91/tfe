<?php

namespace App\Http\Requests\Client\Order;

use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Rules\ValidCompanyDeliveryDate;
use App\Rules\ValidPickupSlot;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderRequest extends FormRequest
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
        $allowedDeliveryPostalCodes = (array) config(
            'restaurant.delivery.own_address.postal_codes',
            ['6700'],
        );

        $allowedDeliveryTypes = [
            DeliveryTypeEnum::OWN_ADDRESS->value,
        ];

        if (
            config(
                'restaurant.delivery.company.enabled',
                false,
            )
        ) {
            $allowedDeliveryTypes[] = DeliveryTypeEnum::COMPANY->value;
        }

        return [
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(
                    fn (Builder $query) => $query->where('user_id', $this->user()->id)
                ),
            ],
            'type' => ['required', Rule::in(OrderTypeEnum::values())],
            'delivery_type' => [
                'nullable',
                'string',
                'required_if:type,'.OrderTypeEnum::DELIVERY->value,
                'prohibited_unless:type,'.OrderTypeEnum::DELIVERY->value,
                Rule::in($allowedDeliveryTypes),
            ],
            'delivery_company_id' => [
                'nullable',
                'integer',
                'required_if:delivery_type,'.
                DeliveryTypeEnum::COMPANY->value,
                'prohibited_unless:delivery_type,'.
                DeliveryTypeEnum::COMPANY->value,
                Rule::exists(
                    'delivery_companies',
                    'id',
                )->where(
                    fn (Builder $query) => $query
                        ->where('is_active', true)
                        ->whereNull('deleted_at'),
                ),
            ],
            'delivery_date' => [
                'nullable',
                'string',
                'date_format:Y-m-d',
                'required_if:delivery_type,'.
                DeliveryTypeEnum::COMPANY->value,
                'prohibited_unless:delivery_type,'.
                DeliveryTypeEnum::COMPANY->value,
                new ValidCompanyDeliveryDate(
                    $this->integer('delivery_company_id') ?: null,
                ),
            ],
            'table_number' => [
                'nullable',
                'string',
                'required_if:type,'.OrderTypeEnum::DINE_IN->value,
                'prohibited_unless:type,'.OrderTypeEnum::DINE_IN->value,
            ],
            'pickup_time' => [
                'nullable',
                'date',
                'required_if:type,'.OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,'.OrderTypeEnum::TAKEAWAY->value,
                new ValidPickupSlot(
                    app(RestaurantAvailabilityService::class),
                ),
            ],
            'pickup_name' => [
                'nullable',
                'string',
                'required_if:type,'.OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,'.OrderTypeEnum::TAKEAWAY->value,
            ],
            'pickup_phone' => [
                'nullable',
                'string',
                'required_if:type,'.OrderTypeEnum::TAKEAWAY->value,
                'prohibited_unless:type,'.OrderTypeEnum::TAKEAWAY->value,
            ],
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(
                    fn (Builder $query) => $query
                        ->where(
                            'user_id',
                            $this->user()->id,
                        )
                        ->whereIn(
                            'postal_code',
                            $allowedDeliveryPostalCodes,
                        ),
                ),
                'required_if:delivery_type,'.
                DeliveryTypeEnum::OWN_ADDRESS->value,
                'prohibited_unless:delivery_type,'.
                DeliveryTypeEnum::OWN_ADDRESS->value,
            ],
            'payment_method' => ['required', Rule::in(PaymentMethodEnum::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'used_points' => ['nullable', 'integer', Rule::in([0, 300, 550])],
        ];
    }
}
