<?php

namespace App\Http\Requests\Admin\DeliveryCompany;

use App\Models\DeliveryCompany;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyDeliveryDateRequest extends FormRequest
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
        /** @var DeliveryCompany $company */
        $company = $this->route('deliveryCompany');

        $earliestDate = CarbonImmutable::now(
            config('restaurant.timezone'),
        )
            ->startOfDay()
            ->addDays($company->minimum_advance_days)
            ->format('Y-m-d');

        return [
            'delivery_date' => [
                'required',
                'date_format:Y-m-d',
                "after_or_equal:{$earliestDate}",
                Rule::unique(
                    'company_delivery_dates',
                    'delivery_date',
                )->where(
                    fn ($query) => $query->where(
                        'delivery_company_id',
                        $company->id,
                    ),
                ),
            ],
            'is_available' => [
                'required',
                'boolean',
            ],
        ];
    }
}
