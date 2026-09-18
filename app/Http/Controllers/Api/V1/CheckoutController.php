<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Actions\Client\Order\Commands\PlaceOrder\PlaceOrder;
use App\Actions\Client\Order\Queries\GetDeliveryOptions;
use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\PlaceOrderRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Http\Resources\Api\V1\CartResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function show(
        Request $request,
        GetOrCreateCart $getOrCreateCart,
        GetDeliveryOptions $getDeliveryOptions,
    ): JsonResponse {
        $user = $request->user();

        $cart = $getOrCreateCart->execute($request);
        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients',
        ]);

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $loyaltyPointBalance = (int) $user
            ->loyaltyPointTransactions()
            ->get()
            ->sum(
                fn (LoyaltyPointTransaction $transaction): int => match (
                    $transaction->type
                ) {
                    LoyaltyPointTransactionTypeEnum::EARNED,
                    LoyaltyPointTransactionTypeEnum::REFUNDED => $transaction->points,
                    LoyaltyPointTransactionTypeEnum::REDEEMED,
                    LoyaltyPointTransactionTypeEnum::REVERSED => -$transaction->points,
                },
            );

        return response()->json([
            'data' => [
                'cart' => new CartResource($cart),
                'addresses' => AddressResource::collection($addresses),
                'order_types' => array_map(
                    fn (OrderTypeEnum $type): array => [
                        'value' => $type->value,
                        'key' => $type->key(),
                        'label' => $type->translatedLabel(),
                    ],
                    OrderTypeEnum::cases()
                ),
                'payment_methods' => array_map(
                    fn (PaymentMethodEnum $method): array => [
                        'value' => $method->value,
                        'key' => $method->key(),
                        'label' => $method->translatedLabel(),
                    ],
                    PaymentMethodEnum::cases()
                ),
                'loyalty_points' => [
                    'balance' => $loyaltyPointBalance,
                    'redemption_options' => [
                        [
                            'points' => 300,
                            'discount' => '5.00',
                        ],
                        [
                            'points' => 550,
                            'discount' => '10.00',
                        ],
                    ],
                ],
                'delivery_fee' => '2.00',
                'delivery_options' => $getDeliveryOptions->execute(),
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function storeOrder(
        PlaceOrderRequest $request,
        PlaceOrder $placeOrder,
    ): JsonResponse {
        $result = $placeOrder->execute($request->validated());

        if ($result['order'] === null) {
            throw ValidationException::withMessages([
                'cart' => $result['message'],
            ]);
        }

        return response()->json([
            'data' => new OrderResource($result['order']),
            'message' => $result['message'],
        ], 201);
    }
}
