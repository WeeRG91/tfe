@php
    $title = __('messages.emails.subjects.completed');
    $headerTitle = __('messages.emails.headers.completed_title');
    $headerSubtitle = __('messages.emails.headers.completed_subtitle');
    $badgeClass = 'badge-completed';
@endphp

@extends('emails.order.order-email-layout')

@section('status-content')
    <div class="section">
        <div class="section-title">
            {{ __('messages.emails.order_summary') }}
        </div>

        <table
            role="presentation"
            width="100%"
            cellpadding="0"
            cellspacing="0"
            style="width: 100%;"
        >
            <tr>
                <td
                    style="
                        padding: 12px 0;
                        border-bottom: 1px solid #e5e7eb;
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    {{ __('messages.emails.completed_at') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 0;
                        border-bottom: 1px solid #e5e7eb;
                        color: #1f2937;
                        font-size: 14px;
                        font-weight: 600;
                    "
                >
                    {{ $order->completed_at
                        ?->locale(app()->getLocale())
                        ->translatedFormat('d F Y, H:i') ?? '—' }}
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding: 16px 0 0;
                        color: #111827;
                        font-size: 16px;
                        font-weight: 700;
                    "
                >
                    {{ __('messages.emails.total') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 16px 0 0;
                        color: #111827;
                        font-size: 18px;
                        font-weight: 700;
                    "
                >
                    €{{ number_format((float) $order->total_inc_vat, 2) }}
                </td>
            </tr>
        </table>
    </div>
@endsection
