<?php

use App\Enums\DishCategoryEnum;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Meat;
use App\Models\User;

it('adds a configured dish to the authenticated mobile users cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Curry description.',
    ]);
    $dish->save();

    $meat = Meat::query()->create(['extra_price' => '2.00']);
    $meat->translateOrNew('en')->fill([
        'name' => 'Chicken',
        'description' => 'Chicken meat.',
    ]);
    $meat->save();
    $dish->meats()->attach($meat);

    $ingredient = Ingredient::query()->create();
    $ingredient->translateOrNew('en')->fill([
        'name' => 'Onion',
        'description' => 'Fresh onion.',
    ]);
    $ingredient->save();
    $dish->ingredients()->attach($ingredient);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/dishes', [
            'dish_id' => $dish->id,
            'meat_id' => $meat->id,
            'quantity' => 2,
            'spicy_level' => 2,
            'removed_ingredient_ids' => [$ingredient->id],
            'notes' => 'Leave at reception',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.items.0.item_type', 'dish')
        ->assertJsonPath('data.items.0.item.id', $dish->id)
        ->assertJsonPath('data.items.0.meat.id', $meat->id)
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.unit_price', '16.50')
        ->assertJsonPath('data.items.0.line_total', '33.00')
        ->assertJsonPath('data.items.0.spicy_level', 2)
        ->assertJsonPath(
            'data.items.0.removed_ingredients.0.id',
            $ingredient->id,
        )
        ->assertJsonPath('data.items.0.notes', 'Leave at reception')
        ->assertJsonPath('data.summary.quantity', 2)
        ->assertJsonPath('data.summary.total', '33.00');

    $cartItemId = $response->json('data.items.0.id');

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItemId,
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'meat_id' => $meat->id,
        'quantity' => 2,
        'unit_price' => '16.50',
        'total' => '33.00',
        'spicy_level' => 2,
        'notes' => 'Leave at reception',
    ]);

    $this->assertDatabaseHas('cart_item_removed_ingredients', [
        'cart_item_id' => $cartItemId,
        'ingredient_id' => $ingredient->id,
    ]);
});

it('merges an identical dish configuration already in the cart', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $dish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);
    $dish->translateOrNew('en')->fill([
        'name' => 'Test dish',
        'description' => 'Test description.',
    ]);
    $dish->save();

    $meat = Meat::query()->create(['extra_price' => '1.50']);
    $meat->translateOrNew('en')->fill([
        'name' => 'Test meat',
        'description' => 'Test meat description.',
    ]);
    $meat->save();
    $dish->meats()->attach($meat);

    $payload = [
        'dish_id' => $dish->id,
        'meat_id' => $meat->id,
        'quantity' => 1,
        'spicy_level' => 1,
        'removed_ingredient_ids' => [],
        'notes' => null,
    ];

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/dishes', $payload)
        ->assertOk();

    $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/dishes', [
            ...$payload,
            'quantity' => 2,
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.items.0.unit_price', '11.50')
        ->assertJsonPath('data.items.0.line_total', '34.50')
        ->assertJsonPath('data.summary.quantity', 3)
        ->assertJsonPath('data.summary.total', '34.50');

    $this->assertDatabaseCount('cart_items', 1);
});

it('rejects unavailable or deleted dishes', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $unavailableDish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 0,
        'is_available' => false,
    ]);

    $deletedDish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 0,
        'is_available' => true,
    ]);
    $deletedDish->delete();

    foreach ([$unavailableDish->id, $deletedDish->id] as $dishId) {
        $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/v1/cart/items/dishes', [
                'dish_id' => $dishId,
                'meat_id' => 1,
                'quantity' => 1,
                'spicy_level' => 0,
                'removed_ingredient_ids' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dish_id');
    }

    $this->assertDatabaseCount('cart_items', 0);
});

it('requires meat and removed ingredients to belong to the dish', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $dish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 0,
        'is_available' => true,
    ]);
    $unrelatedMeat = Meat::query()->create(['extra_price' => '1.00']);
    $unrelatedIngredient = Ingredient::query()->create();

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/dishes', [
            'dish_id' => $dish->id,
            'meat_id' => $unrelatedMeat->id,
            'quantity' => 1,
            'spicy_level' => 0,
            'removed_ingredient_ids' => [$unrelatedIngredient->id],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'meat_id',
            'removed_ingredient_ids.0',
        ]);

    $this->assertDatabaseCount('cart_items', 0);
});

it('validates the configured dish cart fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $this
        ->withToken($token->plainTextToken)
        ->postJson('/api/v1/cart/items/dishes', [
            'quantity' => 0,
            'spicy_level' => 4,
            'notes' => str_repeat('a', 1001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'dish_id',
            'meat_id',
            'quantity',
            'spicy_level',
            'notes',
        ]);
});

it('requires authentication to add a dish to the mobile cart', function () {
    $this->postJson('/api/v1/cart/items/dishes')
        ->assertUnauthorized();
});
