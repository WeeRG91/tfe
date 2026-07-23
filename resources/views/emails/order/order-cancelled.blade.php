@php
    $title = 'Order Cancelled';
    $headerClass = 'header-cancelled';
    $headerTitle = '× Order cancelled!';
    $headerSubtitle = 'Your order has been cancelled!';
    $badgeClass = 'badge-cancelled';
    $sectionTitleClass = 'section-title-cancelled';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Order Summary</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Order Number</div><div class="info-value">#{{ $order->order_number }}</div></div>
            <div class="info-item"><div class="info-label">Cancelled At</div><div class="info-value">{{ $order->cancelled_at ?? now() }}</div></div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>Your order has been cancelled!</strong></p>
@endsection
