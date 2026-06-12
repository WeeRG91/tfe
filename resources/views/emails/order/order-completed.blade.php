@php
    $title = 'Order Completed';
    $headerClass = 'header-completed';
    $headerTitle = '✓ Order Completed!';
    $headerSubtitle = 'Thank you for your order';
    $badgeClass = 'badge-completed';
    $sectionTitleClass = 'section-title-completed';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">Order Summary</div>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Order Number</div><div class="info-value">#{{ $order->order_number }}</div></div>
            <div class="info-item"><div class="info-label">Completed At</div><div class="info-value">{{ $order->completed_at ?? now() }}</div></div>
        </div>
    </div>

    <div class="progress-tracker">
        <div class="progress-steps">
            <div class="progress-line"><div class="progress-line-active" style="width: 100%; background: #9C27B0;"></div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Placed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Confirmed</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Ready</div></div>
            @if($order->type->label() === 'Delivery')
                <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Delivering</div></div>
            @endif
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">Completed</div></div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>Thank you for choosing us!</strong></p>
    <p>We look forward to serving you again.</p>
@endsection
