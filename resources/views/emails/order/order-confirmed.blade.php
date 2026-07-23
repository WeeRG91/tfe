@php
    $title = 'Order Confirmed';
    $headerClass = 'header-confirmed';
    $headerTitle = '✓ Order Confirmed!';
    $headerSubtitle = 'Your order has been successfully confirmed';
    $badgeClass = 'badge-confirmed';
    $sectionTitleClass = 'section-title-confirmed';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Order Details</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Order Type</div><div class="info-value">{{ $order->type->label() }}</div></div>
            <div class="info-item"><div class="info-label">Confirmed At</div><div class="info-value">{{ $order->confirmed_at }}</div></div>
        </div>
    </div>

    <div class="section">
        @if($order->type->label() === 'Dine-in')
            <div class="section-title {{ $sectionTitleClass }}">Table Information</div>
            @if($order->table_number)<div class="info-value" style="font-size: 16px; font-weight: 600;">Table #{{ $order->table_number }}</div>@endif

        @elseif($order->type->label() === 'Takeaway')
            <div class="section-title {{ $sectionTitleClass }}">Pickup Information</div>
            <div class="info-grid">
                @if($order->pickup_time)<div class="info-item"><div class="info-label">Pickup Time</div><div class="info-value">{{ $order->pickup_time }}</div></div>@endif
                @if($order->pickup_name)<div class="info-item"><div class="info-label">Pickup Name</div><div class="info-value">{{ $order->pickup_name }}</div></div>@endif
                @if($order->pickup_phone)<div class="info-item"><div class="info-label">Phone</div><div class="info-value">{{ $order->pickup_phone }}</div></div>@endif
            </div>

        @else
            <div class="section-title {{ $sectionTitleClass }}">Delivery Information</div>
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Name</div><div class="info-value">{{ $order->address->first_name }} {{ $order->address->last_name }}</div></div>
                <div class="info-item"><div class="info-label">Address</div><div class="info-value">{{ $order->address->street }}, {{ $order->address->city }}, {{ $order->address->postal_code }}</div></div>
                <div class="info-item"><div class="info-label">Phone</div><div class="info-value">{{ $order->address->phone }}</div></div>
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Your Order</div>

        <table class="items-table">
            <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center; width: 50px;">Qty</th>
                <th style="text-align: right; width: 80px;">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->item->name }}</div>
                        <div class="item-details">
                            <p><strong>Category:</strong> {{ $item->item->category->label() }}</p>
                            <p class="meat-info">• {{ ['No spicy', 'Mild', 'Spicy', 'Hot'][$item->spicy_level] }}</p>
                            @if($item->meat)
                                <p class="meat-info">✓ {{ $item->meat->name }} @if($item->meat->extra_price > 0)(+€{{ number_format($item->meat->extra_price, 2) }})@endif</p>
                            @endif
                            @if($item->removed_ingredients)
                                <p><strong>Removed:</strong> @foreach($item->removed_ingredients as $ingredient)<span class="removed-ingredient">{{ $ingredient->name }}</span>@if(!$loop->last), @endif @endforeach</p>
                            @endif
                            @if($item->notes)
                                <p><strong>Note:</strong> {{ $item->notes }}</p>
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
        <div class="section-title {{ $sectionTitleClass }}">Payment Summary</div>
        <div class="totals">
            @foreach($order->vat_breakdown as $vat)
                <div class="totals-row"><div class="totals-label">VAT {{ $vat['vat_rate'] }}%:</div><div class="totals-value">€{{ number_format($vat['vat_total'], 2) }}</div></div>
            @endforeach
            <div class="totals-row"><div class="totals-label">Subtotal:</div><div class="totals-value">€{{ number_format($order->subtotal, 2) }}</div></div>
            @if($order->discount_total > 0)
                <div class="totals-row"><div class="totals-label">Discount:</div><div class="totals-value">-€{{ number_format($order->discount_total, 2) }}</div></div>
            @endif
            @if($order->delivery_fee > 0)
                <div class="totals-row"><div class="totals-label">Delivery fee:</div><div class="totals-value">€{{ number_format($order->delivery_fee, 2) }}</div></div>
            @endif
            <div class="totals-row grand-total"><div class="totals-label">Total:</div><div class="totals-value">€{{ number_format($order->total_inc_vat, 2) }}</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Payment Method</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Method</div><div class="info-value">{{ $order->payment_method->label() }}</div></div>
            <div class="info-item"><div class="info-label">Status</div><div class="info-value">{{ $order->payment_status->label() }}</div></div>
            @if($order->paid_at)<div class="info-item"><div class="info-label">Paid At</div><div class="info-value">{{ $order->paid_at }}</div></div>@endif
        </div>
    </div>

    @if($order->notes)
        <div class="section">
            <div class="section-title {{ $sectionTitleClass }}">Special Instructions</div>
            <div class="notes-box">{{ $order->notes }}</div>
        </div>
    @endif
@endsection

@section('footer-message')
    <p><strong>Thank you for your order!</strong></p>
    <p>We'll notify you when your order is ready.</p>
@endsection
