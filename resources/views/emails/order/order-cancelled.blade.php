@php
    use App\Enums\PaymentMethodEnum;
    use App\Enums\PaymentStatusEnum;

    $title = __('messages.emails.subjects.cancelled');
    $headerTitle = __('messages.emails.headers.cancelled_title');
    $headerSubtitle = __('messages.emails.headers.cancelled_subtitle');
    $badgeClass = 'badge-cancelled';

    $paymentMessage = match (true) {
        $order->payment_status === PaymentStatusEnum::REFUNDED =>
            __('messages.emails.cancellation_payment.refunded'),

        $order->payment_status === PaymentStatusEnum::REFUND_PENDING =>
            __('messages.emails.cancellation_payment.refund_pending'),

        $order->payment_status === PaymentStatusEnum::REFUND_FAILED =>
            __('messages.emails.cancellation_payment.refund_failed'),

        $order->payment_method === PaymentMethodEnum::CASH,
        in_array(
            $order->payment_status,
            [
                PaymentStatusEnum::PENDING,
                PaymentStatusEnum::FAILED,
            ],
            true,
        ) =>
            __('messages.emails.cancellation_payment.not_charged'),

        default =>
            __('messages.emails.cancellation_payment.review_order'),
    };
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title">
            {{ __('messages.emails.order_summary') }}
        </div>

        <table
            role="presentation"
            width="100%"
            cellpadding="0"
            cellspacing="0"
            style="width: 100%;"
        >
            <tr>
                <td
                    style="
                        padding: 12px 0;
                        border-bottom: 1px solid #e5e7eb;
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    {{ __('messages.emails.cancelled_at') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 0;
                        border-bottom: 1px solid #e5e7eb;
                        color: #1f2937;
                        font-size: 14px;
                        font-weight: 600;
                    "
                >
                    {{ $order->cancelled_at
                        ?->locale(app()->getLocale())
                        ->translatedFormat('d F Y, H:i') ?? '—' }}
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding: 16px 0 0;
                        color: #111827;
                        font-size: 16px;
                        font-weight: 700;
                    "
                >
                    {{ __('messages.emails.total') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 16px 0 0;
                        color: #111827;
                        font-size: 18px;
                        font-weight: 700;
                    "
                >
                    €{{ number_format((float) $order->total_inc_vat, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div
        style="
            padding: 14px 16px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #7f1d1d;
            font-size: 13px;
            line-height: 1.6;
        "
    >
        <strong>
            {{ __('messages.emails.payment_update') }}
        </strong>

        <br>

        {{ $paymentMessage }}
    </div>
@endsection
