@php
    $title = 'Order Out for Delivery';
    $headerClass = 'header-delivering';
    $headerTitle = '✓ Order Out for Delivery!';
    $headerSubtitle = 'Your order is on its way to you';
    $badgeClass = 'badge-delivering';
    $sectionTitleClass = 'section-title-delivering';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Order Summary</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Order Number</div><div class="info-value">#{{ $order->order_number }}</div></div>
            <div class="info-item"><div class="info-label">Out For Delivery At</div><div class="info-value">{{ $order->delivered_at ?? now() }}</div></div>
        </div>
    </div>

    <div class="progress-tracker">
        <div class="progress-steps">
            <div class="progress-line"><div class="progress-line-active" style="width: 90%; background: #FF9800;"></div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Placed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Confirmed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Ready</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Delivering</div></div>
            <div class="step active"><div class="step-circle"></div><div class="step-label">Completed</div></div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>Your order is out for delivery!</strong></p>
@endsection
