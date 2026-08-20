<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Client\Cart\Commands\AddDishToCart;
use App\Actions\Client\Cart\Commands\AddDrinkToCart;
use App\Actions\Client\Cart\Commands\RemoveCartItem;
use App\Actions\Client\Cart\Commands\UpdateCartItemNotes;
use App\Actions\Client\Cart\Commands\UpdateCartItemQuantity;
use App\Enums\ItemTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\StoreDishCartItemRequest;
use App\Http\Requests\Api\V1\Cart\StoreDrinkCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemNotesRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemQuantityRequest;
use App\Http\Resources\Api\V1\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $request->user()
            ->cart()
            ->firstOrCreate();

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }

    public function storeDish(
        StoreDishCartItemRequest $request,
        AddDishToCart $addDishToCart,
    ): JsonResponse {
        $validated = $request->validated();

        $removedIngredientIds = collect(
            $validated['removed_ingredient_ids'] ?? []
        )
            ->sort()
            ->values()
            ->all();

        $addDishToCart->execute($request, [
            'item_id' => $validated['dish_id'],
            'item_type' => ItemTypeEnum::DISH->value,
            'meat_id' => $validated['meat_id'],
            'quantity' => $validated['quantity'],
            'spicy_level' => $validated['spicy_level'],
            'removed_ingredients' => $removedIngredientIds,
            'notes' => $validated['notes'] ?? null,
        ]);

        $cart = $request->user()
            ->cart()
            ->firstOrFail();

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }

    public function storeDrink(
        StoreDrinkCartItemRequest $request,
        AddDrinkToCart $addDrinkToCart,
    ): JsonResponse {
        $validated = $request->validated();

        $addDrinkToCart->execute($request, [
            'item_id' => $validated['drink_id'],
            'item_type' => ItemTypeEnum::DRINK->value,
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $cart = $request->user()
            ->cart()
            ->firstOrFail();

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }

    public function updateQuantity(
        UpdateCartItemQuantityRequest $request,
        int $cartItemId,
        UpdateCartItemQuantity $updateCartItemQuantity,
    ): JsonResponse {
        $validated = $request->validated();

        $cart = $request->user()
            ->cart()
            ->firstOrFail();

        $cart->items()->findOrFail($cartItemId);

        $updateCartItemQuantity->execute(
            $request,
            $cartItemId,
            $validated['action']
        );

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }

    public function updateNotes(
        UpdateCartItemNotesRequest $request,
        int $cartItemId,
        UpdateCartItemNotes $updateCartItemNotes,
    ): JsonResponse {
        $validated = $request->validated();

        $cart = $request->user()
            ->cart()
            ->firstOrFail();

        $cart->items()->findOrFail($cartItemId);

        $updateCartItemNotes->execute(
            $request,
            $cartItemId,
            $validated['notes'],
        );

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }

    public function destroyItem(
        Request $request,
        int $cartItemId,
        RemoveCartItem $removeCartItem
    ): JsonResponse {
        $cart = $request->user()
            ->cart()
            ->firstOrFail();

        $cart->items()->findOrFail($cartItemId);

        $removeCartItem->execute($request, $cartItemId);

        $cart->load([
            'items.item',
            'items.meat',
            'items.removedIngredients.allergen',
        ]);

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(200);
    }
}
