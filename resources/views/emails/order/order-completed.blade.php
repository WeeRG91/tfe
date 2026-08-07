@php
    $title = __('messages.emails.subjects.completed');
    $headerClass = 'header-completed';
    $headerTitle = '✓ ' . __('messages.emails.headers.completed_title');
    $headerSubtitle = __('messages.emails.headers.completed_subtitle');
    $badgeClass = 'badge-completed';
    $sectionTitleClass = 'section-title-completed';
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
                <div class="info-label">{{ __('messages.emails.completed_at') }}</div>
                <div class="info-value">
                    {{ $order->completed_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    <div class="progress-tracker">
        <div class="progress-steps">
            <div class="progress-line"><div class="progress-line-active" style="width: 100%; background: #9C27B0;"></div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">{{ __('messages.emails.placed') }}</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">{{ __('messages.emails.confirmed') }}</div></div>
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">{{ __('messages.emails.ready') }}</div></div>
            @if($order->type === \App\Enums\OrderTypeEnum::DELIVERY)
                <div class="step completed"><div class="step-circle">✓</div><div class="step-label">{{ __('messages.emails.delivering') }}</div></div>
            @endif
            <div class="step completed"><div class="step-circle">✓</div><div class="step-label">{{ __('messages.emails.completed') }}</div></div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>{{ __('messages.emails.footer.thank_choice') }}</strong></p>
    <p>{{ __('messages.emails.footer.serve_again') }}</p>
@endsection
