<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Payment\Commands\CreatePaymentIntent;
use App\Actions\Client\Payment\Commands\HandleStripeWebhook;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Stripe\Exception\ApiErrorException;
use Throwable;

class PaymentController extends Controller
{
    public function payment(Order $order): JsonResponse|InertiaResponse
    {
        if ($order->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => __('messages.payment.unauthorized'),
            ]);
        }

        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return Inertia::render('client/Payment', [
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Order $order, CreatePaymentIntent $createPaymentIntent): JsonResponse
    {
        $this->authorize('pay', $order);

        $results = $createPaymentIntent->execute($order);

        return response()->json([
            'message' => $results['message'],
            'client_secret' => $results['client_secret'],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function stripeWebhook(Request $request, HandleStripeWebhook $handleStripeWebhook): JsonResponse
    {
        $result = $handleStripeWebhook->execute($request);

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    public function paymentSuccess(Order $order): JsonResponse|InertiaResponse
    {
        if ($order->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => __('messages.payment.unauthorized'),
            ]);
        }

        $order->load('user', 'items.item', 'items.removedIngredients', 'address');

        return Inertia::render('client/PaymentSuccess', [
            'orderToPay' => new OrderResource($order),
        ]);
    }
}
