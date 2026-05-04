<?php

namespace App\Http\Controllers\Client;

use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Address\AddressResource;
use App\Http\Resources\Client\Cart\CartResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CartController extends Controller
{
    protected function getCart(Request $request)
    {
        $guestToken = $request->header('X-Guest-Token');

        if (auth()->check()) {
            $userId = auth()->user()->id;
            $existingUserCart = Cart::query()->where('user_id', $userId)->first();

            if ($existingUserCart) {
                return $existingUserCart;
            }

            if ($guestToken) {
                $existingGuestCart = Cart::query()->where('guest_token', $guestToken)->first();

                if ($existingGuestCart) {
                    $existingGuestCart->update([
                        'user_id' => $userId,
                        'guest_token' => null,
                    ]);

                    return $existingGuestCart;
                }
            }

            return Cart::query()->create([
                'user_id' => $userId,
            ]);
        }

        if (!$guestToken) {
            $guestToken = Str::uuid()->toString();
        }

        return Cart::query()->firstOrCreate(['guest_token' => $guestToken]);
    }

    protected function updatePrices(CartItem $item)
    {
        $basePrice = $item->item->price ?? 0;
        $meatPrice = $item->meat->extra_price ?? 0;

        $unitPrice = $basePrice + $meatPrice;

        $item->update([
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $item->quantity,
        ]);
    }

    public function cart(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('user', 'items.item', 'items.meat', 'items.removedIngredients');

        return response()->json(new CartResource($cart));
    }

    public function checkout()
    {
        return Inertia::render('client/Checkout');
    }

    public function placeOrder()
    {
        $addresses = Address::query()->where('user_id', auth()->user()->id)->orderBy('is_default', 'desc')->get();

        return Inertia::render('client/PlaceOrder', [
            'orderTypes' => OrderTypeEnum::getTypes(),
            'paymentMethods' => PaymentMethodEnum::getPaymentMethods(),
            'addresses' => AddressResource::collection($addresses)->collection,
        ]);
    }

    public function addDish(Request $request)
    {
        $cart = $this->getCart($request);

        $validated = $request->validate([
            'item_id' => 'required',
            'item_type' => 'required|in:dish,drink',
            'meat_id' => 'required|exists:meats,id',
            'quantity' => 'required|integer|min:1',
            'removed_ingredients' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $itemToAdd = $cart->items()
            ->where('item_id', $validated['item_id'])
            ->where('item_type', $validated['item_type'])
            ->where('meat_id', $validated['meat_id'])
            ->with('removedIngredients')
            ->get();

        $existingItem = $itemToAdd->first(function ($cartItem) use ($validated) {
            $existingRemovedIngredients = $cartItem->removedIngredients->pluck('id')->values();
            $newRemovedIngredients = collect($validated['removed_ingredients'] ?? [])->sort()->values();

            return $existingRemovedIngredients->values()->all() === $newRemovedIngredients->values()->all();
        });

        if ($existingItem) {
            $existingItem->increment('quantity', $validated['quantity']);
            $existingItem->load('item', 'meat');
            $this->updatePrices($existingItem);

            $itemName = $existingItem->item->name ?? 'Item';
            return response()->json([
                'message' => "{$itemName} added successfully to cart",
            ]);
        }

        $item = $cart->items()->create([
            'item_id' => $validated['item_id'],
            'item_type' => $validated['item_type'],
            'meat_id' => $validated['meat_id'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'],
        ]);

        if (!empty($validated['removed_ingredients'])) {
            $item->removedIngredients()->sync($validated['removed_ingredients']);
        }

        $item->load('item', 'meat');
        $this->updatePrices($item);

        $itemName = $item->item->name ?? 'Item';
        return response()->json([
            'message' => "{$itemName} added successfully to cart",
        ]);
    }

    public function addDrink(Request $request)
    {
        $cart = $this->getCart($request);

        $validated = $request->validate([
            'item_id' => 'required',
            'item_type' => 'required|in:dish,drink',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $existingItem = $cart->items()
            ->where('item_id', $validated['item_id'])
            ->where('item_type', $validated['item_type'])
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $validated['quantity']);
            $existingItem->load('item', 'meat');
            $this->updatePrices($existingItem);

            $itemName = $existingItem->item->name ?? 'Item';
            return response()->json([
                'message' => "{$itemName} added successfully to cart",
            ]);
        }

        $item = $cart->items()->create([
            'item_id' => $validated['item_id'],
            'item_type' => $validated['item_type'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'],
        ]);

        $item->load('item', 'meat');
        $this->updatePrices($item);

        $itemName = $item->item->name ?? 'Item';
        return response()->json([
            'message' => "{$itemName} added successfully to cart",
        ]);
    }

    public function removeItem(Request $request, int $cartItemId)
    {
        $cart = $this->getCart($request);

        $cartItem = $cart->items()->find($cartItemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        $cartItem->load('item');

        $itemName = $cartItem->item->name ?? 'Item';

        $cartItem->delete();

        return response()->json([
            'message' => "{$itemName} removed successfully from cart"
        ]);
    }

    public function updateNotes(Request $request, int $cartItemId)
    {
        $cart = $this->getCart($request);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $cartItem = $cart->items()->find($cartItemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        $cartItem->update(['notes' => $validated['notes']]);

        return response()->noContent();
    }

    public function updateQuantity(Request $request, int $cartItemId)
    {
        $cart = $this->getCart($request);

        $validated = $request->validate([
            'action' => 'required|string|in:increase,decrease',
        ]);

        $cartItem = $cart->items()->find($cartItemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        if ($validated['action'] === 'increase') {
            $cartItem->increment('quantity');
        } else {
            if ($cartItem->quantity <= 1) {
                $cartItem->delete();

                return response()->json([
                    'message' => 'Item removed from cart'
                ]);
            }

            $cartItem->decrement('quantity');
        }

        $cartItem->load('item', 'meat');
        $this->updatePrices($cartItem);

        return response()->noContent();
    }
}
