@php
    $title = 'Order Ready';
    $headerClass = 'header-ready';
    $headerTitle = '✓ Order Ready!';
    $headerSubtitle = 'Your order is prepared and waiting';
    $badgeClass = 'badge-ready';
    $sectionTitleClass = 'section-title-ready';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Order Summary</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Order Number</div><div class="info-value">#{{ $order->order_number }}</div></div>
            <div class="info-item"><div class="info-label">Ready At</div><div class="info-value">{{ $order->ready_at ?? now() }}</div></div>
        </div>
    </div>

    <div class="progress-tracker">
        <div class="progress-steps">
            <div class="progress-line"><div class="progress-line-active" style="width: 66%; background: #2196F3;"></div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Placed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Confirmed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Ready</div></div>
            @if($order->type->label() === 'Delivery')
                <div class="step active"><div class="step-circle"></div><div class="step-label">Delivering</div></div>
            @endif
            <div class="step active"><div class="step-circle"></div><div class="step-label">Completed</div></div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>Your order is ready!</strong></p>
@endsection
