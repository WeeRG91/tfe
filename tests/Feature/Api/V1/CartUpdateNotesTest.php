<?php

use App\Enums\DrinkCategoryEnum;
use App\Models\Cart;
use App\Models\Drink;
use App\Models\User;

it('updates an owned mobile cart items notes', function () {
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
        'quantity' => 1,
        'unit_price' => '4.00',
        'total' => '4.00',
        'notes' => null,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/notes", [
            'notes' => 'Please add ice',
        ])
        ->assertOk()
        ->assertJsonPath(
            'data.items.0.notes',
            'Please add ice',
        );

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'notes' => 'Please add ice',
    ]);
});

it('clears an owned mobile cart items notes', function () {
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
        'quantity' => 1,
        'unit_price' => '4.00',
        'total' => '4.00',
        'notes' => 'Please add ice',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/notes", [
            'notes' => null,
        ])
        ->assertOk()
        ->assertJsonPath('data.items.0.notes', null);

    expect($cartItem->refresh()->notes)->toBeNull();
});

it('does not update another users cart item notes', function () {
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
        'notes' => null,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/notes", [
            'notes' => 'Changed by another user',
        ])
        ->assertNotFound();

    expect($cartItem->refresh()->notes)->toBeNull();
});

it('validates mobile cart item notes', function () {
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
        'quantity' => 1,
        'unit_price' => '4.00',
        'total' => '4.00',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/notes", [
            'notes' => str_repeat('a', 1001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('notes');
});

it('requires authentication to update mobile cart item notes', function () {
    $this->patchJson('/api/v1/cart/items/1/notes', [
        'notes' => 'Please add ice',
    ])->assertUnauthorized();
});
