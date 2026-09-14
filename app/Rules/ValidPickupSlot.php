<?php

namespace App\Rules;

use App\Services\RestaurantAvailabilityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Throwable;

readonly class ValidPickupSlot implements ValidationRule
{
    public function __construct(
        private RestaurantAvailabilityService $availability,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        try {
            $pickupTime = $this->availability->parsePickupTime($value);
        } catch (Throwable) {
            $fail('The pickup time is invalid.');

            return;
        }

        if (! $this->availability->isValidPickupSlot($pickupTime)) {
            $fail(
                'The selected pickup time is no longer available.',
            );
        }
    }
}
