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
@endsection

@section('footer-message')
    <p><strong>Thank you for your order!</strong></p>
    <p>We'll notify you when your order is ready.</p>
@endsection
