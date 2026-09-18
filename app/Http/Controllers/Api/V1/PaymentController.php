<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Client\Payment\Commands\CreatePaymentIntent;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    /**
     * @throws ApiErrorException
     */
    public function storePaymentIntent(
        Order $order,
        CreatePaymentIntent $createPaymentIntent,
    ) {
        $this->authorize('pay', $order);

        if ($order->payment_method !== PaymentMethodEnum::CARD) {
            throw ValidationException::withMessages([
                'payment_method' => __(
                    'messages.payment.card_required',
                ),
            ]);
        }

        $result = $createPaymentIntent->execute($order);

        return response()->json([
            'data' => [
                'client_secret' => $result['client_secret'],
            ],
            'message' => $result['message'],
        ]);
    }
}
