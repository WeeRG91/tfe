<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Confirmation - # {{ $order->order_number }}</title>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 20px;
        }
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #4CAF50;
            border-left: 3px solid #4CAF50;
            padding-left: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 14px;
            color: #333;
        }
        .confirmation-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            width: 100%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: left;
            padding: 10px;
            background-color: #f9f9f9;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        .item-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .item-details p {
            margin: 3px 0;
        }
        .meat-info {
            color: #ff9800;
        }
        .removed-ingredient {
            color: #f44336;
            text-decoration: line-through;
        }
        .totals {
            text-align: right;
            margin-top: 15px;
        }
        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }
        .totals-label {
            font-weight: 600;
            margin-right: 20px;
            min-width: 120px;
            text-align: left;
        }
        .totals-value {
            min-width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 18px;
            font-weight: 700;
            color: #4CAF50;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e0e0e0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .content {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .totals-row {
                flex-direction: column;
                align-items: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Order Confirmed!</h1>
            <p>Your order has been successfully confirmed</p>
        </div>

        <div class="content">
            <!-- Order Number & Status -->
            <div class="section">
                <div class="confirmation-badge">
                    Order #{{ $order->order_number }} • {{ $order->status->label }}
                </div>
            </div>

            <!-- Order Type & Details -->
            <div class="section">
                <div class="section-title">Order Details</div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Order Type</div>
                        <div class="info-value">{{ $order->type->label }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Confirmed At</div>
                        <div class="info-value">{{ $order->confirmed_at }}</div>
                    </div>
                </div>
            </div>

            <div class="section">
                @if($order->type->label === 'Dine-in')
                    <div class="section-title">Table Information</div>
                    <div class="info-grid">
                        @if($order->table_number)
                            <div class="info-item">
                                <div class="info-label">Table Number</div>
                                <div class="info-value">{{ $order->table_number }}</div>
                            </div>
                        @endif
                    </div>
                @elseif($order->type->label === 'Takeaway')
                    <div class="section-title">Pickup Information</div>
                    <div class="info-grid">
                        @if($order->pickup_time)
                            <div class="info-item">
                                <div class="info-label">Pickup Time</div>
                                <div class="info-value">{{ $order->pickup_time }}</div>
                            </div>
                        @endif
                        @if($order->pickup_name)
                            <div class="info-item">
                                <div class="info-label">Pickup Name</div>
                                <div class="info-value">{{ $order->pickup_name }}</div>
                            </div>
                        @endif
                        @if($order->pickup_phone)
                            <div class="info-item">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value">{{ $order->pickup_phone }}</div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="section-title">Delivery Information</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Name</div>
                            <div class="info-value">{{ $order->delivery_address->first_name }} {{ $order->delivery_address->last_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Delivery Address</div>
                            <div class="info-value">{{ $order->delivery_address->street }}, {{ $order->delivery_address->city }}, {{ $order->delivery_address->country }}, {{ $order->delivery_address->postal_code }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">{{ $order->delivery_address->phone }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="section">
                <div class="section-title">Your Order</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center">Qty</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="item-name">{{ $item->item->name }}</div>
                                    <div class="item-details">
                                        <p><strong>Category: </strong>{{ $item->item->category->label }}</p>

                                        @if($item->meat)
                                            <p class="meat-info">
                                                ✓ {{ $item->meat->name }}
                                                @if($item->meat->extra_price > 0)
                                                    (+€{{ number_format($item->meat->extra_price, 2) }})
                                                @endif
                                            </p>
                                        @endif

                                        @if($item->removed_ingredients)
                                            <p>
                                                <strong>Removed: </strong>
                                                @foreach($item->removed_ingredients as $ingredient)
                                                    <span class="removed-ingredient">{{ $ingredient->name }}@if(!$loop->last), @endif</span>
                                                @endforeach
                                            </p>
                                        @endif

                                        @if($item->notes)
                                            <p><strong>Note: </strong>{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle">
                                    {{ $item->quantiy }}
                                </td>
                                <td style="text-align: right; vertical-align: middle">
                                    €{{ number_format($item->total_inc_vat, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Payment Summary -->
            <div class="section">
                <div class="section-title">Payment Summary</div>
                <div class="totals">
                    @foreach($order->vat_breakdown as $vat)
                        <div class="totals-row">
                            <div class="totals-label">Vat {{ $vat->vat_rate }}%:</div>
                            <div class="totals-value">{{ number_format($vat->vat_total, 2) }}</div>
                        </div>
                    @endforeach
                    @if($order->vat_total > 0)
                        <div class="totals-row">
                            <div class="totals-label">Vat total:</div>
                            <div class="totals-value">{{ number_format($order->vat_total, 2) }}</div>
                        </div>
                    @endif
                    <div class="totals-row">
                        <div class="totals-label">Subtotal:</div>
                        <div class="totals-value">€{{ number_format($order->subtotal, 2) }}</div>
                    </div>
                    @if($order->discout_total > 0)
                        <div class="totals-row">
                            <div class="totals-label">Discount:</div>
                            <div class="totals-value">{{ number_format($order->discount_total, 2) }}</div>
                        </div>
                    @endif
                    @if($order->delivery_fee > 0)
                        <div class="totals-row">
                            <div class="totals-label">Delivery fee:</div>
                            <div class="totals-value">{{ number_format($order->delivery_fee, 2) }}</div>
                        </div>
                    @endif
                    <div class="totals-row grand-total">
                        <div class="totals-label">Total:</div>
                        <div class="totals-value">{{ number_format($order->total_inc_vat, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="section">
                <div class="section-title">Payment Method</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Method</div>
                        <div class="info-value">{{ $order->payment_method->label }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ $order->payment_status->label }}</div>
                    </div>
                    @if($order->paid_at)
                        <div class="info-item">
                            <div class="info-label">Paid At</div>
                            <div class="info-value">{{ $order->paid_at }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
                <div class="section">
                    <div class="section-title">Order Notes</div>
                    <div class="info-value">{{ $order->notes }}</div>
                </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>Thank you for your order!</strong></p>
            <p>We'll notify you when your order is ready.</p>
            <p style="margin-top: 15px; font-size: 11px;">
                If you have any questions, please contact us.<br>
                © {{ date('Y') }} Your Restaurant
            </p>
        </div>
    </div>
</body>
</html>
