<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum PaymentStatusEnum: int
{
    case PENDING = 1;
    case PAID = 2;
    case FAILED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
        };
    }

    public static function getPaymentStatuses(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }

    public static function getPaymentStatus(self $case): array
    {
        return Arr::first(
            self::getPaymentStatuses(),
            fn($item) => $item['value'] === $case->value,
        );
    }
}
