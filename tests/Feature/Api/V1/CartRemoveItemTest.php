<?php

use App\Enums\DrinkCategoryEnum;
use App\Models\Cart;
use App\Models\Drink;
use App\Models\User;

it('removes an owned item from the mobile cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $drink = Drink::query()->create([
        'price' => '4.00',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);

    $cartItem = $cart->items()->create([
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 2,
        'unit_price' => '4.00',
        'total' => '8.00',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/cart/items/{$cartItem->id}")
        ->assertOk()
        ->assertJsonCount(0, 'data.items')
        ->assertJsonPath('data.summary.quantity', 0)
        ->assertJsonPath('data.summary.total', '0.00');

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
});

it('does not remove another users cart item', function () {
    $owner = User::factory()->withoutTwoFactor()->create();
    $attacker = User::factory()->withoutTwoFactor()->create();
    $ownerCart = Cart::query()->create(['user_id' => $owner->id]);
    $token = $attacker->createToken('Test phone', ['mobile']);

    $drink = Drink::query()->create([
        'price' => '4.00',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);

    $cartItem = $ownerCart->items()->create([
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 1,
        'unit_price' => '4.00',
        'total' => '4.00',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/cart/items/{$cartItem->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
    ]);
});

it('returns not found for a missing mobile cart item', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    Cart::query()->create(['user_id' => $user->id]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson('/api/v1/cart/items/999999')
        ->assertNotFound();
});

it('requires authentication to remove a mobile cart item', function () {
    $this->deleteJson('/api/v1/cart/items/1')
        ->assertUnauthorized();
});
