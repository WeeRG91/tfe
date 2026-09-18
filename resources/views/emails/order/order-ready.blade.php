@php
    use App\Enums\DeliveryTypeEnum;
    use App\Enums\OrderTypeEnum;

    $title = __('messages.emails.subjects.ready');
    $headerTitle = __('messages.emails.headers.ready_title');
    $badgeClass = 'badge-ready';

    $headerSubtitle = match ($order->type) {
        OrderTypeEnum::DINE_IN =>
            __('messages.emails.ready_messages.dine_in'),

        OrderTypeEnum::TAKEAWAY =>
            __('messages.emails.ready_messages.takeaway'),

        OrderTypeEnum::DELIVERY =>
            $order->delivery_type === DeliveryTypeEnum::COMPANY
                ? __('messages.emails.ready_messages.company_delivery')
                : __('messages.emails.ready_messages.address_delivery'),
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
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    {{ __('messages.emails.ready_at') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 0;
                        color: #1f2937;
                        font-size: 14px;
                        font-weight: 600;
                    "
                >
                    {{ $order->ready_at
                        ?->locale(app()->getLocale())
                        ->translatedFormat('d F Y, H:i') ?? '—' }}
                </td>
            </tr>
        </table>
    </div>

    @include('emails.order.partials.fulfilment-details', [
        'order' => $order,
    ])
@endsection
