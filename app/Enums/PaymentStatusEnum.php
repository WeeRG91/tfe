<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum PaymentStatusEnum: int
{
    case PENDING = 1;
    case PAID = 2;
    case FAILED = 3;
    case REFUND_PENDING = 4;
    case REFUNDED = 5;
    case REFUND_FAILED = 6;

    public function key(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::PAID => 'paid',
            self::FAILED => 'failed',
            self::REFUND_PENDING => 'refundPending',
            self::REFUNDED => 'refunded',
            self::REFUND_FAILED => 'refundFailed',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUND_PENDING => 'Refund pending',
            self::REFUNDED => 'Refunded',
            self::REFUND_FAILED => 'Refund failed',
        };
    }

    public function translatedLabel(): string
    {
        return __('messages.enums.payment_status.'.$this->key());
    }

    public static function getPaymentStatuses(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'key' => $case->key(),
            'label' => $case->label(),
        ], self::cases());
    }

    public static function getPaymentStatus(self $case): array
    {
        return Arr::first(
            self::getPaymentStatuses(),
            fn ($item) => $item['value'] === $case->value,
        );
    }

    public static function fromStripeRefundStatus(
        string $status
    ): self {
        return match ($status) {
            'succeeded' => self::REFUNDED,

            'pending',
            'requires_action' => self::REFUND_PENDING,

            'failed',
            'canceled' => self::REFUND_FAILED,
        };
    }
}
