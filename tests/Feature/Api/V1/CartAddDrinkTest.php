<?php

use App\Enums\DrinkCategoryEnum;
use App\Models\Drink;
use App\Models\User;

it('adds a drink to the authenticated mobile users cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $drink = Drink::query()->create([
        'price' => '5.25',
        'category' => DrinkCategoryEnum::SMOOTHIE,
        'is_available' => true,
        'main_image' => 'drinks/mango-smoothie.jpg',
    ]);
    $drink->translateOrNew('en')->fill([
        'name' => 'Mango smoothie',
        'description' => 'Fresh mango smoothie.',
    ]);
    $drink->save();

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'drink_id' => $drink->id,
            'quantity' => 2,
            'notes' => 'No straw',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.items.0.item_type', 'drink')
        ->assertJsonPath('data.items.0.item.id', $drink->id)
        ->assertJsonPath('data.items.0.item.name', 'Mango smoothie')
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.unit_price', '5.25')
        ->assertJsonPath('data.items.0.line_total', '10.50')
        ->assertJsonPath('data.items.0.spicy_level', null)
        ->assertJsonPath('data.items.0.meat', null)
        ->assertJsonCount(0, 'data.items.0.removed_ingredients')
        ->assertJsonPath('data.items.0.notes', 'No straw')
        ->assertJsonPath('data.summary.quantity', 2)
        ->assertJsonPath('data.summary.total', '10.50');

    $this->assertDatabaseHas('cart_items', [
        'item_id' => $drink->id,
        'item_type' => Drink::class,
        'quantity' => 2,
        'unit_price' => '5.25',
        'total' => '10.50',
        'notes' => 'No straw',
    ]);
});

it('merges a drink already in the cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $drink = Drink::query()->create([
        'price' => '3.50',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);
    $drink->translateOrNew('en')->fill([
        'name' => 'Lemon soda',
        'description' => 'Sparkling lemon soda.',
    ]);
    $drink->save();

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'drink_id' => $drink->id,
            'quantity' => 1,
            'notes' => null,
        ])
        ->assertOk();

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'drink_id' => $drink->id,
            'quantity' => 2,
            'notes' => null,
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.items.0.unit_price', '3.50')
        ->assertJsonPath('data.items.0.line_total', '10.50')
        ->assertJsonPath('data.summary.quantity', 3)
        ->assertJsonPath('data.summary.total', '10.50');

    $this->assertDatabaseCount('cart_items', 1);
});

it('rejects unavailable or deleted drinks', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $unavailableDrink = Drink::query()->create([
        'price' => '3.50',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => false,
    ]);

    $deletedDrink = Drink::query()->create([
        'price' => '3.50',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);
    $deletedDrink->delete();

    foreach ([$unavailableDrink->id, $deletedDrink->id] as $drinkId) {
        $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/v1/cart/items/drinks', [
                'drink_id' => $drinkId,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('drink_id');
    }

    $this->assertDatabaseCount('cart_items', 0);
});

it('validates the drink cart fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/drinks', [
            'quantity' => 0,
            'notes' => str_repeat('a', 1001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'drink_id',
            'quantity',
            'notes',
        ]);
});

it('requires authentication to add a drink to the mobile cart', function () {
    $this->postJson('/api/v1/cart/items/drinks')
        ->assertUnauthorized();
});
