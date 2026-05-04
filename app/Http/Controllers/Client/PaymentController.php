<?php

namespace App\Http\Controllers\Client;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public  function payment(Order $order)
    {
        if ($order->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ]);
        }

        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return Inertia::render('client/Payment', [
            'order' => new OrderResource($order),
        ]);
    }

    public function createPaymentIntent(Order $order)
    {
        if ($order->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            return response()->json([
                'message' => 'Order already paid',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret_key'));

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($order->total_price * 100),
            'currency' => 'eur',
            'payment_method_types' => ['card', 'bancontact'],
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]
        ]);

        return response()->json([
            'client_secret' => $paymentIntent?->client_secret,
        ]);
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('stripe-signature');

        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'message' => 'Invalid signature',
                'error' => $e->getMessage(),
                'signature' => $signature,
            ], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $order = Order::query()->findOrFail($paymentIntent->metadata->order_id);

                $order?->update([
                    'payment_status' => PaymentStatusEnum::PAID,
                    'status' => OrderStatusEnum::CONFIRMED,
                    'confirmed_at' => now(),
                    'paid_at' => now(),
                ]);

                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;

                $order = Order::query()->findOrFail($paymentIntent->metadata->order_id);

                $order?->update([
                    'payment_status' => PaymentStatusEnum::FAILED,
                ]);

                break;
        }

        return response()->json([
            'message' => 'Payment confirmed',
        ]);
    }

    public function paymentSuccess(Order $order)
    {
        if ($order->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ]);
        }

        $order->load('user', 'items.item', 'items.removedIngredients', 'address');

        return Inertia::render('client/PaymentSuccess', [
            'orderToPay' => new OrderResource($order),
        ]);
    }
}
