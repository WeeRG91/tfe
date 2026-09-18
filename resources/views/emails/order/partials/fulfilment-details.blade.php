@php
    use App\Enums\DeliveryTypeEnum;
    use App\Enums\OrderTypeEnum;

    $isDineIn = $order->type === OrderTypeEnum::DINE_IN;
    $isTakeaway = $order->type === OrderTypeEnum::TAKEAWAY;
    $isDelivery = $order->type === OrderTypeEnum::DELIVERY;

    $isCompanyDelivery =
        $isDelivery &&
        $order->delivery_type === DeliveryTypeEnum::COMPANY;

    $isAddressDelivery =
        $isDelivery &&
        ! $isCompanyDelivery;
@endphp

<div class="section">
    <div class="section-title">
        {{ __('messages.emails.fulfilment_details') }}
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        style="
            width: 100%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        "
    >
        <tr>
            <td
                style="
                    padding: 12px 16px;
                    border-bottom: 1px solid #e5e7eb;
                    color: #6b7280;
                    font-size: 13px;
                "
            >
                {{ __('messages.emails.order_type') }}
            </td>

            <td
                align="right"
                style="
                    padding: 12px 16px;
                    border-bottom: 1px solid #e5e7eb;
                    color: #1f2937;
                    font-size: 13px;
                    font-weight: 600;
                "
            >
                {{ $order->type->translatedLabel() }}
            </td>
        </tr>

        @if ($isDineIn)
            <tr>
                <td
                    style="
                        padding: 12px 16px;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    {{ __('messages.emails.table_information') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 16px;
                        color: #1f2937;
                        font-size: 13px;
                        font-weight: 600;
                    "
                >
                    @if ($order->table_number)
                        {{ __('messages.emails.table_number', [
                            'number' => $order->table_number,
                        ]) }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endif

        @if ($isTakeaway)
            @if ($order->pickup_time)
                <tr>
                    <td
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #6b7280;
                            font-size: 13px;
                        "
                    >
                        {{ __('messages.emails.pickup_time') }}
                    </td>

                    <td
                        align="right"
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #1f2937;
                            font-size: 13px;
                            font-weight: 600;
                        "
                    >
                        {{ $order->pickup_time
                            ->locale(app()->getLocale())
                            ->translatedFormat('d F Y, H:i') }}
                    </td>
                </tr>
            @endif

            @if ($order->pickup_name)
                <tr>
                    <td
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #6b7280;
                            font-size: 13px;
                        "
                    >
                        {{ __('messages.emails.pickup_name') }}
                    </td>

                    <td
                        align="right"
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #1f2937;
                            font-size: 13px;
                            font-weight: 600;
                        "
                    >
                        {{ $order->pickup_name }}
                    </td>
                </tr>
            @endif

            @if ($order->pickup_phone)
                <tr>
                    <td
                        style="
                            padding: 12px 16px;
                            color: #6b7280;
                            font-size: 13px;
                        "
                    >
                        {{ __('messages.emails.phone') }}
                    </td>

                    <td
                        align="right"
                        style="
                            padding: 12px 16px;
                            color: #1f2937;
                            font-size: 13px;
                            font-weight: 600;
                        "
                    >
                        <a
                            href="tel:{{ $order->pickup_phone }}"
                            style="color: #1f2937; text-decoration: none;"
                        >
                            {{ $order->pickup_phone }}
                        </a>
                    </td>
                </tr>
            @endif
        @endif

        @if ($isCompanyDelivery)
            <tr>
                <td
                    style="
                        padding: 12px 16px;
                        border-bottom: 1px solid #e5e7eb;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    {{ __('messages.emails.delivery_method') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 16px;
                        border-bottom: 1px solid #e5e7eb;
                        color: #1f2937;
                        font-size: 13px;
                        font-weight: 600;
                    "
                >
                    {{ __('messages.emails.company_delivery') }}
                </td>
            </tr>

            @if ($order->delivery_company_name)
                <tr>
                    <td
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #6b7280;
                            font-size: 13px;
                        "
                    >
                        {{ __('messages.emails.delivery_company') }}
                    </td>

                    <td
                        align="right"
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #1f2937;
                            font-size: 13px;
                            font-weight: 600;
                        "
                    >
                        {{ $order->delivery_company_name }}
                    </td>
                </tr>
            @endif

            <tr>
                <td
                    style="
                        padding: 12px 16px;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    {{ __('messages.emails.delivery_date') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 16px;
                        color: #1f2937;
                        font-size: 13px;
                        font-weight: 600;
                    "
                >
                    @if ($order->delivery_date)
                        {{ $order->delivery_date
                            ->locale(app()->getLocale())
                            ->translatedFormat('d F Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endif

        @if ($isAddressDelivery)
            <tr>
                <td
                    style="
                        padding: 12px 16px;
                        border-bottom: 1px solid #e5e7eb;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    {{ __('messages.emails.delivery_method') }}
                </td>

                <td
                    align="right"
                    style="
                        padding: 12px 16px;
                        border-bottom: 1px solid #e5e7eb;
                        color: #1f2937;
                        font-size: 13px;
                        font-weight: 600;
                    "
                >
                    {{ __('messages.emails.delivery_to_address') }}
                </td>
            </tr>

            @if ($order->address)
                <tr>
                    <td
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #6b7280;
                            font-size: 13px;
                            vertical-align: top;
                        "
                    >
                        {{ __('messages.emails.address') }}
                    </td>

                    <td
                        align="right"
                        style="
                            padding: 12px 16px;
                            border-bottom: 1px solid #e5e7eb;
                            color: #1f2937;
                            font-size: 13px;
                            font-weight: 600;
                            vertical-align: top;
                        "
                    >
                        {{ $order->address->first_name }}
                        {{ $order->address->last_name }}
                        <br>

                        {{ $order->address->street }}
                        <br>

                        {{ $order->address->postal_code }}
                        {{ $order->address->city }}
                    </td>
                </tr>

                @if ($order->address->phone)
                    <tr>
                        <td
                            style="
                                padding: 12px 16px;
                                color: #6b7280;
                                font-size: 13px;
                            "
                        >
                            {{ __('messages.emails.phone') }}
                        </td>

                        <td
                            align="right"
                            style="
                                padding: 12px 16px;
                                color: #1f2937;
                                font-size: 13px;
                                font-weight: 600;
                            "
                        >
                            <a
                                href="tel:{{ $order->address->phone }}"
                                style="color: #1f2937; text-decoration: none;"
                            >
                                {{ $order->address->phone }}
                            </a>
                        </td>
                    </tr>
                @endif
            @else
                <tr>
                    <td
                        colspan="2"
                        style="
                            padding: 12px 16px;
                            color: #991b1b;
                            font-size: 13px;
                        "
                    >
                        {{ __('messages.emails.delivery_details_unavailable') }}
                    </td>
                </tr>
            @endif
        @endif
    </table>
</div>
