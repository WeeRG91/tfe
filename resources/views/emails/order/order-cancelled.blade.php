@php
    $title = __('messages.emails.subjects.cancelled');
    $headerClass = 'header-cancelled';
    $headerTitle = '× ' . __('messages.emails.headers.cancelled_title');
    $headerSubtitle = __('messages.emails.headers.cancelled_subtitle');
    $badgeClass = 'badge-cancelled';
    $sectionTitleClass = 'section-title-cancelled';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title {{ $sectionTitleClass }}">
            {{ __('messages.emails.order_summary') }}
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">{{ __('messages.emails.order_number') }}</div>
                <div class="info-value">#{{ $order->order_number }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">{{ __('messages.emails.cancelled_at') }}</div>
                <div class="info-value">
                    {{ $order->cancelled_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>{{ __('messages.emails.footer.cancelled') }}</strong></p>
@endsection
