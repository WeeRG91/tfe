@php
    use App\Enums\DeliveryTypeEnum;

    $title = __('messages.emails.subjects.delivering');
    $headerTitle = __('messages.emails.headers.delivering_title');
    $badgeClass = 'badge-delivering';

    $headerSubtitle =
        $order->delivery_type === DeliveryTypeEnum::COMPANY
            ? __('messages.emails.delivering_messages.company_delivery')
            : __('messages.emails.delivering_messages.address_delivery');
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
                    {{ __('messages.emails.out_for_delivery_at') }}
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
                    {{ $order->delivered_at
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
