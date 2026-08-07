<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? __('messages.emails.order_update') }} - #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            padding: 24px 30px;
            text-align: center;
            color: white;
        }

        .header-confirmed { background-color: #4caf50; }
        .header-ready { background-color: #2196f3; }
        .header-delivering { background-color: #ff9800; }
        .header-completed { background-color: #9c27b0; }
        .header-cancelled { background-color: #ff0000; }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 20px 30px;
        }

        .section {
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
            padding-left: 8px;
        }

        .section-title-confirmed { color: #4caf50; border-left: 3px solid #4caf50; }
        .section-title-ready { color: #2196f3; border-left: 3px solid #2196f3; }
        .section-title-delivering { color: #ff9800; border-left: 3px solid #ff9800; }
        .section-title-completed { color: #9c27b0; border-left: 3px solid #9c27b0; }
        .section-title-cancelled { color: #ff0000; border-left: 3px solid #ff0000; }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }

        .badge-confirmed { background-color: #4CAF50; color: white; }
        .badge-ready { background-color: #2196f3; color: white; }
        .badge-delivering { background-color: #ff9800; color: white; }
        .badge-completed { background-color: #9c27b0; color: white; }
        .badge-cancelled { background-color: #ff0000; color: white; }

        .status-card {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            margin-bottom: 12px;
        }

        .status-icon {
            font-size: 36px;
            margin-bottom: 4px;
        }

        .status-message {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        .info-grid  {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 12px;
        }

        .info-item {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 13px;
            color: #333;
            line-height: 1.4;
        }

        .progress-tracker {
            margin: 16px 0 12px;
        }

        .progress-steps {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            position: relative;
            gap: 8px;
        }

        .progress-line {
            position: absolute;
            top: 16px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }

        .progress-line-active {
            background: currentColor;
            height: 100%;
            width: var(--progress-width, 0%);
            transition: width 0.3s ease;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
            background: white;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            margin: 0 auto 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            color: #999;
        }

        .step.completed .step-circle {
            background: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }

        .step.active .step-circle {
            border-color: #FF9800;
            border-width: 2px;
            color: #FF9800;
            font-weight: bold;
        }

        .step-label {
            font-size: 10px;
            color: #999;
            font-weight: 500;
        }

        .step.completed .step-label,
        .step.active .step-label {
            color: #666;
            font-weight: 600;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            text-align: left;
            padding: 6px 6px;
            background-color: #fafafa;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .item-name {
            font-weight: 700;
            margin-bottom: 4px;
            color: #333;
            font-size: 14px;
        }

        .item-details {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        .item-details p {
            margin: 2px 0;
        }

        .item-details strong {
            color: #666;
        }

        .meat-info {
            color: #ff9800;
        }

        .removed-ingredient {
            color: #f44336;
            text-decoration: line-through;
        }

        .totals {
            margin-top: 8px;
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .totals-label {
            font-weight: 500;
            margin-right: 16px;
            min-width: 100px;
            text-align: left;
            color: #666;
        }

        .totals-value {
            min-width: 80px;
            text-align: right;
            font-weight: 500;
        }

        .grand-total {
            font-size: 15px;
            font-weight: 800;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #e0e0e0;
        }

        .grand-total .totals-label, .grand-total .totals-value {
            color: #4caf50;
            font-weight: 800;
        }

        .action-button {
            text-align: center;
            margin: 12px 0;
        }

        .btn {
            display: inline-block;
            padding: 8px 24px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            background-color: #4caf50;
            color: white;
        }

        .notes-box {
            background-color: #fafafa;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 16px 20px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #eaeaea;
        }

        .footer p {
            margin: 4px 0;
        }

        .footer strong {
            color: #4caf50;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                margin: 0;
            }
            .content {
                padding: 16px 20px;
            }
            .header {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
                gap: 4px;
            }
            .totals-row {
                justify-content: space-between;
            }
            .totals-label {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $headerClass }}">
            <h1>{{ $headerTitle }}</h1>
            <p>{{ $headerSubtitle }}</p>
        </div>

        <div class="content">
            <div class="section" style="border-bottom: none; padding-bottom: 0;">
                <div class="badge {{ $badgeClass }}">
                    {{ __('messages.emails.order', ['number' => $order->order_number]) }}
                    •
                    {{ $order->status->translatedLabel() }}
                </div>
            </div>

            @yield('status-content')

            <div class="action-button">
                <a href="{{ route('order.order-details', $order) }}" class="btn">
                    {{ __('messages.emails.view_order') }}
                </a>
            </div>
        </div>

        <div class="footer">
            @yield('footer-message')
            <p style="margin-top: 8px; font-size: 10px;">
                © {{ date('Y') }} {{ __('messages.emails.restaurant_name') }}
                •
                <a href="mailto:hello@yourrestaurant.com" style="color: #999;">
                    {{ __('messages.emails.contact_us') }}
                </a>
            </p>
        </div>
    </div>
</body>
</html>
