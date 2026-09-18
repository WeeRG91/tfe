<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $title ?? __('messages.emails.order_update') }}
        - #{{ $order->order_number }}
    </title>

    <style>
        body {
            width: 100% !important;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: #1f2937;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            max-width: 100%;
            border: 0;
        }

        a {
            color: #dc2626;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f9fafb;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
        }

        .email-card {
            overflow: hidden;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .brand {
            padding: 24px 32px 20px;
            border-bottom: 1px solid #e5e7eb;
            color: #dc2626;
            font-size: 22px;
            font-weight: 700;
            text-align: left;
        }

        .content {
            padding: 32px;
        }

        .status-badge {
            display: inline-block;
            margin-bottom: 16px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

        .badge-confirmed,
        .badge-ready,
        .badge-delivering,
        .badge-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .email-title {
            margin: 0;
            color: #111827;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.25;
        }

        .email-subtitle {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        .order-reference {
            margin: 20px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .order-reference strong {
            color: #374151;
        }

        .email-body {
            padding-top: 28px;
        }

        .section {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: 0;
        }

        .section-title {
            margin: 0 0 14px;
            padding: 0;
            border: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
        }

        .section-title-confirmed,
        .section-title-ready,
        .section-title-delivering,
        .section-title-completed,
        .section-title-cancelled {
            color: #111827;
            border: 0;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-item {
            display: table-cell;
            width: 50%;
            padding: 0 16px 12px 0;
            vertical-align: top;
        }

        .info-label {
            margin-bottom: 3px;
            color: #6b7280;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .info-value {
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
        }

        .items-table th {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 11px;
            font-weight: 600;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 12px 6px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 13px;
            vertical-align: top;
        }

        .item-name {
            color: #1f2937;
            font-size: 14px;
            font-weight: 600;
        }

        .item-details {
            margin-top: 4px;
            color: #6b7280;
            font-size: 12px;
        }

        .item-details p {
            margin: 3px 0;
        }

        .removed-ingredient {
            color: #991b1b;
            text-decoration: line-through;
        }

        .totals {
            width: 100%;
        }

        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 7px;
        }

        .totals-label,
        .totals-value {
            display: table-cell;
            font-size: 13px;
        }

        .totals-label {
            color: #6b7280;
            text-align: left;
        }

        .totals-value {
            color: #374151;
            font-weight: 500;
            text-align: right;
        }

        .grand-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .grand-total .totals-label,
        .grand-total .totals-value {
            color: #111827;
            font-size: 16px;
            font-weight: 700;
        }

        .notes-box,
        .status-card {
            padding: 14px 16px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #374151;
            font-size: 13px;
        }

        /*
         * Temporary compatibility for the existing status templates.
         * We will remove these progress styles when those templates
         * are simplified in later steps.
         */
        .progress-tracker {
            margin: 24px 0;
        }

        .progress-steps {
            display: table;
            width: 100%;
        }

        .progress-line {
            display: none;
        }

        .step {
            display: table-cell;
            text-align: center;
            vertical-align: top;
        }

        .step-circle {
            width: 24px;
            height: 24px;
            margin: 0 auto 6px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            color: #9ca3af;
            font-size: 11px;
            font-weight: 700;
            line-height: 24px;
            text-align: center;
        }

        .step.completed .step-circle {
            background-color: #16a34a;
            border-color: #16a34a;
            color: #ffffff;
        }

        .step.active .step-circle {
            border-color: #16a34a;
            color: #16a34a;
        }

        .step-label {
            color: #6b7280;
            font-size: 10px;
        }

        .action {
            padding-top: 28px;
            text-align: left;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background-color: #dc2626;
            border-radius: 8px;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }

        .footer {
            padding: 22px 32px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.6;
            text-align: left;
        }

        .footer p {
            margin: 0 0 6px;
        }

        .footer p:last-child {
            margin-bottom: 0;
        }

        .footer-message {
            margin-bottom: 12px;
            color: #4b5563;
        }

        .footer-contact {
            color: #6b7280;
            text-decoration: underline;
        }

        @media only screen and (max-width: 620px) {
            .email-padding {
                padding: 12px !important;
            }

            .brand {
                padding: 20px 22px;
            }

            .content {
                padding: 24px 22px;
            }

            .footer {
                padding: 20px 22px;
            }

            .email-title {
                font-size: 23px;
            }

            .info-item {
                display: block;
                width: 100%;
                padding-right: 0;
            }

            .button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div
    style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
        "
>
    {{ $headerSubtitle }}
    {{ __('messages.emails.order', [
        'number' => $order->order_number,
    ]) }}
</div>

<table
    role="presentation"
    class="email-wrapper"
    width="100%"
    cellpadding="0"
    cellspacing="0"
>
    <tr>
        <td
            class="email-padding"
            align="center"
            style="padding: 32px 16px;"
        >
            <table
                role="presentation"
                class="email-container"
                width="100%"
                cellpadding="0"
                cellspacing="0"
            >
                <tr>
                    <td class="email-card">
                        <div class="brand">
                            {{ config('restaurant.name') }}
                        </div>

                        <div class="content">
                                <span class="status-badge {{ $badgeClass }}">
                                    {{ $emailStatus->translatedLabel() }}
                                </span>

                            <h1 class="email-title">
                                {{ $headerTitle }}
                            </h1>

                            <p class="email-subtitle">
                                {{ $headerSubtitle }}
                            </p>

                            <p class="order-reference">
                                <strong>
                                    {{ __('messages.emails.order', [
                                        'number' => $order->order_number,
                                    ]) }}
                                </strong>
                            </p>

                            <div class="email-body">
                                @yield('status-content')
                            </div>

                            <div class="action">
                                <a
                                    href="{{ route('order.order-details', $order) }}"
                                    class="button"
                                >
                                    {{ __('messages.emails.view_order') }}
                                </a>
                            </div>
                        </div>

                        <div class="footer">
                            @hasSection('footer-message')
                                <div class="footer-message">
                                    @yield('footer-message')
                                </div>
                            @endif

                            <p>
                                {{ config('restaurant.name') }}
                            </p>

                            <p>
                                <a
                                    href="mailto:{{ config('restaurant.contact_email') }}"
                                    class="footer-contact"
                                >
                                    {{ config('restaurant.contact_email') }}
                                </a>
                            </p>

                            <p>
                                © {{ now()->year }}
                                {{ config('restaurant.name') }}
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
