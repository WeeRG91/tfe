<?php

namespace App\Rules;

use App\Models\DeliveryCompany;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Throwable;

readonly class ValidCompanyDeliveryDate implements ValidationRule
{
    public function __construct(
        private ?int $deliveryCompanyId,
    ) {}

    /**
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! is_string($value) ||
            $value === '' ||
            ! $this->deliveryCompanyId
        ) {
            return;
        }

        $company = DeliveryCompany::query()
            ->whereKey($this->deliveryCompanyId)
            ->where('is_active', true)
            ->first();

        if (! $company) {
            return;
        }

        try {
            $deliveryDate = CarbonImmutable::createFromFormat(
                'Y-m-d',
                $value,
                config('restaurant.timezone'),
            )->startOfDay();
        } catch (Throwable) {
            return;
        }

        $earliestDate = CarbonImmutable::now(
            config('restaurant.timezone'),
        )
            ->startOfDay()
            ->addDays(
                $company->minimum_advance_days,
            );

        if ($deliveryDate->lessThan($earliestDate)) {
            $fail(
                'The selected company delivery date does not provide enough advance notice.',
            );

            return;
        }

        $isAvailable = $company
            ->deliveryDates()
            ->where('is_available', true)
            ->whereDate(
                'delivery_date',
                $deliveryDate->format('Y-m-d'),
            )
            ->exists();

        if (! $isAvailable) {
            $fail(
                'The selected company delivery date is not available.',
            );
        }
    }
}
