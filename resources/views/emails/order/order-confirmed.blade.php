@php
    $title = __('messages.emails.subjects.confirmed');
    $headerClass = 'header-confirmed';
    $headerTitle = __('messages.emails.headers.confirmed_title');
    $headerSubtitle = __('messages.emails.headers.confirmed_subtitle');
    $badgeClass = 'badge-confirmed';
    $sectionTitleClass = 'section-title-confirmed';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">
            {{ __('messages.emails.order_details') }}
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">{{ __('messages.emails.order_type') }}</div>
                <div class="info-value">{{ $order->type->translatedLabel() }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">{{ __('messages.emails.confirmed_at') }}</div>
                <div class="info-value">
                    {{ $order->confirmed_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        @if($order->type === \App\Enums\OrderTypeEnum::DINE_IN)
            <div class="section-title {{ $sectionTitleClass }}">
                {{ __('messages.emails.table_information') }}
            </div>
            @if($order->table_number)
                <div class="info-value" style="font-size: 16px; font-weight: 600;">
                    {{ __('messages.emails.table_number', ['number' => $order->table_number]) }}
                </div>
            @endif

        @elseif($order->type === \App\Enums\OrderTypeEnum::TAKEAWAY)
            <div class="section-title {{ $sectionTitleClass }}">
                {{ __('messages.emails.pickup_information') }}
            </div>
            <div class="info-grid">
                @if($order->pickup_time)
                    <div class="info-item">
                        <div class="info-label">{{ __('messages.emails.pickup_time') }}</div>
                        <div class="info-value">
                            {{ $order->pickup_time->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}
                        </div>
                    </div>
                @endif
                @if($order->pickup_name)
                    <div class="info-item">
                        <div class="info-label">{{ __('messages.emails.pickup_name') }}</div>
                        <div class="info-value">{{ $order->pickup_name }}</div>
                    </div>
                @endif
                @if($order->pickup_phone)
                    <div class="info-item">
                        <div class="info-label">{{ __('messages.emails.phone') }}</div>
                        <div class="info-value">{{ $order->pickup_phone }}</div>
                    </div>
                @endif
            </div>

        @else
            <div class="section-title {{ $sectionTitleClass }}">
                {{ __('messages.emails.delivery_information') }}
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">{{ __('messages.emails.name') }}</div>
                    <div class="info-value">{{ $order->address->first_name }} {{ $order->address->last_name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('messages.emails.address') }}</div>
                    <div class="info-value">{{ $order->address->street }}, {{ $order->address->city }}, {{ $order->address->postal_code }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">{{ __('messages.emails.phone') }}</div>
                    <div class="info-value">{{ $order->address->phone }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">
            {{ __('messages.emails.your_order') }}
        </div>

        <table class="items-table">
            <thead>
            <tr>
                <th>{{ __('messages.emails.item') }}</th>
                <th style="text-align: center; width: 50px;">{{ __('messages.emails.quantity') }}</th>
                <th style="text-align: right; width: 80px;">{{ __('messages.emails.total') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->item->name }}</div>
                        <div class="item-details">
                            <p>
                                <strong>{{ __('messages.emails.category') }}:</strong>
                                {{ $item->item->category->translatedLabel() }}
                            </p>
                            <p class="meat-info">
                                •
                                {{ __('messages.emails.spicy_levels.' . [
                                    0 => 'none',
                                    1 => 'mild',
                                    2 => 'spicy',
                                    3 => 'hot',
                                ][$item->spicy_level]) }}
                            </p>
                            @if($item->meat)
                                <p class="meat-info">✓ {{ $item->meat->name }} @if($item->meat->extra_price > 0)(+€{{ number_format($item->meat->extra_price, 2) }})@endif</p>
                            @endif
                            @if($item->removed_ingredients)
                                <p><strong>{{ __('messages.emails.removed') }}:</strong> @foreach($item->removed_ingredients as $ingredient)<span class="removed-ingredient">{{ $ingredient->name }}</span>@if(!$loop->last), @endif @endforeach</p>
                            @endif
                            @if($item->notes)
                                <p><strong>{{ __('messages.emails.note') }}:</strong> {{ $item->notes }}</p>
                            @endif
                        </div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">{{ $item->quantity }}</td>
                    <td style="text-align: right; vertical-align: middle;">€{{ number_format($item->total_inc_vat, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">
            {{ __('messages.emails.payment_summary') }}
        </div>
        <div class="totals">
            @foreach($order->vat_breakdown as $vat)
                <div class="totals-row"><div class="totals-label">{{ __('messages.emails.vat', ['rate' => $vat['vat_rate']]) }}:</div><div class="totals-value">€{{ number_format($vat['vat_total'], 2) }}</div></div>
            @endforeach
            <div class="totals-row"><div class="totals-label">{{ __('messages.emails.subtotal') }}:</div><div class="totals-value">€{{ number_format($order->subtotal, 2) }}</div></div>
            @if($order->discount_total > 0)
                <div class="totals-row"><div class="totals-label">{{ __('messages.emails.discount') }}:</div><div class="totals-value">-€{{ number_format($order->discount_total, 2) }}</div></div>
            @endif
            @if($order->delivery_fee > 0)
                <div class="totals-row"><div class="totals-label">{{ __('messages.emails.delivery_fee') }}:</div><div class="totals-value">€{{ number_format($order->delivery_fee, 2) }}</div></div>
            @endif
            <div class="totals-row grand-total"><div class="totals-label">{{ __('messages.emails.total') }}:</div><div class="totals-value">€{{ number_format($order->total_inc_vat, 2) }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">
            {{ __('messages.emails.payment_method') }}
        </div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">{{ __('messages.emails.method') }}</div><div class="info-value">{{ $order->payment_method->translatedLabel() }}</div></div>
            <div class="info-item"><div class="info-label">{{ __('messages.emails.status') }}</div><div class="info-value">{{ $order->payment_status->translatedLabel() }}</div></div>
            @if($order->paid_at)<div class="info-item"><div class="info-label">{{ __('messages.emails.paid_at') }}</div><div class="info-value">{{ $order->paid_at->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}</div></div>@endif
        </div>
    </div>

    @if($order->notes)
        <div class="section">
            <div class="section-title {{ $sectionTitleClass }}">
                {{ __('messages.emails.special_instructions') }}
            </div>
            <div class="notes-box">{{ $order->notes }}</div>
        </div>
    @endif
@endsection

@section('footer-message')
    <p><strong>{{ __('messages.emails.footer.thank_order') }}</strong></p>
    <p>{{ __('messages.emails.footer.notify_ready') }}</p>
@endsection
