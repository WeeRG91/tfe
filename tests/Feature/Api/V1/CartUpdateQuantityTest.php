<?php

use App\Enums\DrinkCategoryEnum;
use App\Models\Cart;
use App\Models\Drink;
use App\Models\User;

it('increases an owned cart item quantity and recalculates its total', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $drink = Drink::query()->create([
        'price' => '5.00',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);
    $drink->translateOrNew('en')->fill([
        'name' => 'Lemon soda',
        'description' => 'Sparkling lemon soda.',
    ]);
    $drink->save();

    $cartItem = $cart->items()->create([
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 2,
        'unit_price' => '5.00',
        'total' => '10.00',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/quantity", [
            'action' => 'increase',
        ])
        ->assertOk()
        ->assertJsonPath('data.items.0.id', $cartItem->id)
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.items.0.unit_price', '5.00')
        ->assertJsonPath('data.items.0.line_total', '15.00')
        ->assertJsonPath('data.summary.quantity', 3)
        ->assertJsonPath('data.summary.total', '15.00');

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 3,
        'total' => '15.00',
    ]);
});

it('decreases an owned cart item quantity', function () {
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
        'quantity' => 3,
        'unit_price' => '4.00',
        'total' => '12.00',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/quantity", [
            'action' => 'decrease',
        ])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.line_total', '8.00')
        ->assertJsonPath('data.summary.quantity', 2)
        ->assertJsonPath('data.summary.total', '8.00');
});

it('removes a cart item when decreasing its quantity from one', function () {
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
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/quantity", [
            'action' => 'decrease',
        ])
        ->assertOk()
        ->assertJsonCount(0, 'data.items')
        ->assertJsonPath('data.summary.quantity', 0)
        ->assertJsonPath('data.summary.total', '0.00');

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
});

it('does not update another users cart item quantity', function () {
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
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/quantity", [
            'action' => 'increase',
        ])
        ->assertNotFound();

    expect($cartItem->refresh()->quantity)->toBe(1);
});

it('validates the cart quantity action', function () {
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
        ->patchJson("/api/v1/cart/items/{$cartItem->id}/quantity", [
            'action' => 'invalid',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('action');
});

it('requires authentication to update a mobile cart item quantity', function () {
    $this->patchJson('/api/v1/cart/items/1/quantity', [
        'action' => 'increase',
    ])->assertUnauthorized();
});
