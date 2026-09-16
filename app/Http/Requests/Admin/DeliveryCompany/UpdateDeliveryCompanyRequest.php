<?php

namespace App\Http\Requests\Admin\DeliveryCompany;

use App\Models\DeliveryCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryCompanyRequest extends FormRequest
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

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'delivery_companies',
                    'name',
                )
                    ->whereNull('deleted_at')
                    ->ignore($company),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'minimum_advance_days' => [
                'required',
                'integer',
                'min:0',
                'max:365',
            ],
        ];
    }
}
