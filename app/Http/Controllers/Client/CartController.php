<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Cart\Commands\AddDishToCart;
use App\Actions\Client\Cart\Commands\AddDrinkToCart;
use App\Actions\Client\Cart\Commands\RemoveCartItem;
use App\Actions\Client\Cart\Commands\UpdateCartItemNotes;
use App\Actions\Client\Cart\Commands\UpdateCartItemQuantity;
use App\Actions\Client\Cart\Queries\GetOrCreateCart;
use App\Actions\Client\Order\Queries\GetDeliveryOptions;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Cart\AddDishToCartRequest;
use App\Http\Requests\Client\Cart\AddDrinkToCartRequest;
use App\Http\Resources\Client\Address\AddressResource;
use App\Http\Resources\Client\Cart\CartResource;
use App\Http\Resources\Client\LoyaltyPointTransaction\LoyaltyPointTransactionResource;
use App\Models\Address;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CartController extends Controller
{
    public function getCart(
        Request $request,
        GetOrCreateCart $getOrCreateCart
    ): JsonResponse {
        $cart = $getOrCreateCart->execute($request);
        $cart->load('user', 'items.item', 'items.meat', 'items.removedIngredients');

        return response()->json(new CartResource($cart));
    }

    public function checkout(): InertiaResponse
    {
        return Inertia::render('client/Checkout');
    }

    public function placeOrder(
        GetDeliveryOptions $getDeliveryOptions,
    ): InertiaResponse {
        $addresses = Address::query()
            ->where('user_id', auth()->user()->id)
            ->orderByDesc('is_default')
            ->get();

        $loyaltyPointTransactions = LoyaltyPointTransaction::query()
            ->with('order:id,order_number')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('client/PlaceOrder', [
            'orderTypes' => OrderTypeEnum::getTypes(),
            'paymentMethods' => PaymentMethodEnum::getPaymentMethods(),
            'addresses' => AddressResource::collection($addresses)->collection,
            'loyaltyPointTransactions' => LoyaltyPointTransactionResource::collection($loyaltyPointTransactions)->collection,
            'deliveryOptions' => $getDeliveryOptions->execute(),
        ]);
    }

    public function addDish(
        AddDishToCartRequest $request,
        AddDishToCart $addDishToCart
    ): JsonResponse {
        $validated = $request->validated();

        $item = $addDishToCart->execute($request, $validated);

        return response()->json([
            'message' => __('messages.cart.added', [
                'item' => $item->item->name ?? __('messages.cart.item'),
            ]),
        ]);
    }

    public function addDrink(
        AddDrinkToCartRequest $request,
        AddDrinkToCart $addDrinkToCart
    ): JsonResponse {
        $validated = $request->validated();

        $item = $addDrinkToCart->execute($request, $validated);

        return response()->json([
            'message' => __('messages.cart.added', [
                'item' => $item->item->name ?? __('messages.cart.item'),
            ]),
        ]);
    }

    public function removeItem(
        Request $request,
        int $cartItemId,
        RemoveCartItem $removeCartItem
    ): JsonResponse {
        $itemName = $removeCartItem->execute($request, $cartItemId);

        return response()->json([
            'message' => __('messages.cart.removed', [
                'item' => $itemName,
            ]),
        ]);
    }

    public function updateNotes(
        Request $request,
        int $cartItemId,
        UpdateCartItemNotes $updateNotes
    ): HttpResponse {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $updateNotes->execute($request, $cartItemId, $validated['notes']);

        return response()->noContent();
    }

    public function updateQuantity(
        Request $request,
        int $cartItemId,
        UpdateCartItemQuantity $updateQuantity
    ): JsonResponse|HttpResponse {
        $validated = $request->validate([
            'action' => 'required|string|in:increase,decrease',
        ]);

        $result = $updateQuantity->execute($request, $cartItemId, $validated['action']);

        if ($result === 'deleted') {
            return response()->json([
                'message' => __('messages.cart.removed', [
                    'item' => __('messages.cart.item'),
                ]),
            ]);
        }

        return response()->noContent();
    }
}
