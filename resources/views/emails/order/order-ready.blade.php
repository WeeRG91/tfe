@php
    $title = __('messages.emails.subjects.ready');
    $headerClass = 'header-ready';
    $headerTitle = __('messages.emails.headers.ready_title');
    $headerSubtitle = __('messages.emails.headers.ready_subtitle');
    $badgeClass = 'badge-ready';
    $sectionTitleClass = 'section-title-ready';
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
                <div class="info-label">{{ __('messages.emails.ready_at') }}</div>
                <div class="info-value">
                    {{ $order->ready_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    <div class="progress-tracker">
        <div class="progress-steps">
            <div class="progress-line">
                <div class="progress-line-active" style="width: 66%; background: #2196F3;"></div>
            </div>
            <div class="step completed">
                <div class="step-circle">✓</div>
                <div class="step-label">{{ __('messages.emails.placed') }}</div>
            </div>
            <div class="step completed">
                <div class="step-circle">✓</div>
                <div class="step-label">{{ __('messages.emails.confirmed') }}</div>
            </div>
            <div class="step completed">
                <div class="step-circle">✓</div>
                <div class="step-label">{{ __('messages.emails.ready') }}</div>
            </div>
            @if($order->type === \App\Enums\OrderTypeEnum::DELIVERY)
                <div class="step active">
                    <div class="step-circle"></div>
                    <div class="step-label">{{ __('messages.emails.delivering') }}</div>
                </div>
            @endif
            <div class="step active">
                <div class="step-circle"></div>
                <div class="step-label">{{ __('messages.emails.completed') }}</div>
            </div>
        </div>
    </div>
@endsection

@section('footer-message')
    <p><strong>{{ __('messages.emails.footer.ready') }}</strong></p>
@endsection
