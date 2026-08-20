<?php

use App\Enums\DishCategoryEnum;
use App\Models\Cart;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Meat;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('returns an empty cart for an authenticated mobile user', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/cart');

    $response
        ->assertOk()
        ->assertJsonPath('data.id', fn ($id) => is_int($id))
        ->assertJsonCount(0, 'data.items')
        ->assertJsonPath('data.summary.quantity', 0)
        ->assertJsonPath('data.summary.total', '0.00');

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'guest_token' => null,
    ]);
});

it('returns the authenticated users cart items and summary', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 2,
        'is_available' => true,
        'main_image' => 'dishes/chicken-curry.jpg',
    ]);

    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with vegetables and curry sauce.',
    ]);
    $dish->save();

    $cartItem = $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 2,
        'unit_price' => '14.50',
        'total' => '29.00',
        'spicy_level' => 2,
        'notes' => 'No onions',
    ]);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/cart', [
            'Accept-Language' => 'en',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $cart->id)
        ->assertJsonPath('data.items.0.id', $cartItem->id)
        ->assertJsonPath('data.items.0.item_type', 'dish')
        ->assertJsonPath('data.items.0.item.id', $dish->id)
        ->assertJsonPath('data.items.0.item.name', 'Chicken curry')
        ->assertJsonPath(
            'data.items.0.item.image_url',
            Storage::disk('public')->url('dishes/chicken-curry.jpg'),
        )
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.unit_price', '14.50')
        ->assertJsonPath('data.items.0.line_total', '29.00')
        ->assertJsonPath('data.items.0.spicy_level', 2)
        ->assertJsonPath('data.items.0.notes', 'No onions')
        ->assertJsonPath('data.items.0.meat', null)
        ->assertJsonCount(0, 'data.items.0.removed_ingredients')
        ->assertJsonPath('data.summary.quantity', 2)
        ->assertJsonPath('data.summary.total', '29.00');
});

it('does not expose another users cart', function () {
    $owner = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $otherCart = Cart::query()->create(['user_id' => $otherUser->id]);
    $token = $owner->createToken('Test phone', ['mobile']);

    $response = $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/cart');

    $response
        ->assertOk()
        ->assertJsonPath('data.id', fn ($id) => $id !== $otherCart->id)
        ->assertJsonCount(0, 'data.items');

    $this->assertDatabaseHas('carts', [
        'user_id' => $owner->id,
    ]);
});

it('returns translated cart customizations and adjusted prices', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $cart = Cart::query()->create(['user_id' => $user->id]);

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
    $dish->translateOrNew('fr')->fill([
        'name' => 'Curry de poulet',
        'description' => 'Description du curry.',
    ]);
    $dish->save();

    $meat = Meat::query()->create([
        'extra_price' => '2.00',
        'main_image' => 'images/meat/chicken.jpg',
    ]);
    $meat->translateOrNew('en')->fill([
        'name' => 'Chicken',
        'description' => 'Chicken meat.',
    ]);
    $meat->translateOrNew('fr')->fill([
        'name' => 'Poulet',
        'description' => 'Viande de poulet.',
    ]);
    $meat->save();

    $ingredient = Ingredient::query()->create([
        'main_image' => 'images/ingredient/onion.jpg',
    ]);
    $ingredient->translateOrNew('en')->fill([
        'name' => 'Onion',
        'description' => 'Fresh onion.',
    ]);
    $ingredient->translateOrNew('fr')->fill([
        'name' => 'Oignon',
        'description' => 'Oignon frais.',
    ]);
    $ingredient->save();

    $cartItem = $cart->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'meat_id' => $meat->id,
        'quantity' => 2,
        'unit_price' => '16.50',
        'total' => '33.00',
        'spicy_level' => 1,
    ]);
    $cartItem->removedIngredients()->attach($ingredient);

    $this
        ->withToken($token->plainTextToken)
        ->getJson('/api/v1/cart', [
            'Accept-Language' => 'fr',
        ])
        ->assertOk()
        ->assertJsonPath('data.items.0.item.name', 'Curry de poulet')
        ->assertJsonPath('data.items.0.meat.id', $meat->id)
        ->assertJsonPath('data.items.0.meat.name', 'Poulet')
        ->assertJsonPath('data.items.0.meat.extra_price', '2.00')
        ->assertJsonPath(
            'data.items.0.removed_ingredients.0.id',
            $ingredient->id,
        )
        ->assertJsonPath(
            'data.items.0.removed_ingredients.0.name',
            'Oignon',
        )
        ->assertJsonPath(
            'data.items.0.removed_ingredients.0.image_url',
            Storage::disk('public')->url(
                'images/ingredient/onion.jpg',
            ),
        )
        ->assertJsonPath('data.items.0.unit_price', '16.50')
        ->assertJsonPath('data.items.0.line_total', '33.00')
        ->assertJsonPath('data.summary.quantity', 2)
        ->assertJsonPath('data.summary.total', '33.00');
});

it('requires authentication to view a mobile cart', function () {
    $this->getJson('/api/v1/cart')
        ->assertUnauthorized();
});
