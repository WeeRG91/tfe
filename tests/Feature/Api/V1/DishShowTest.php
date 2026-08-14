<?php

use App\Enums\DishCategoryEnum;
use App\Models\Allergen;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Meat;
use App\Models\User;

it('returns a public dish detail as json', function () {
    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 2,
        'is_available' => true,
    ]);

    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with vegetables and curry sauce.',
    ]);

    $dish->save();

    $response = $this->getJson(
        "/api/v1/dishes/{$dish->id}",
        ['Accept-Language' => 'en'],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $dish->id)
        ->assertJsonPath('data.name', 'Chicken curry')
        ->assertJsonPath(
            'data.description',
            'Chicken with vegetables and curry sauce.',
        )
        ->assertJsonPath('data.price', '14.50')
        ->assertJsonPath('data.is_available', true)
        ->assertJsonPath('data.category.value', 2)
        ->assertJsonPath('data.category.key', 'mainCourse')
        ->assertJsonPath('data.default_spicy_level', 2);
});

it('does not return a soft-deleted dish', function () {
    $dish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::APPETIZER,
        'default_spicy_level' => 0,
        'is_available' => true,
    ]);

    $dish->delete();

    $this->getJson("/api/v1/dishes/{$dish->id}")
        ->assertNotFound();
});

it('returns ingredients allergens meats and rating summary', function () {
    $allergen = Allergen::query()->create();

    $allergen->translateOrNew('en')->fill([
        'name' => 'Peanut',
        'description' => 'Contains peanuts.',
    ]);

    $allergen->save();

    $ingredient = Ingredient::query()->create([
        'allergen_id' => $allergen->id,
    ]);

    $ingredient->translateOrNew('en')->fill([
        'name' => 'Peanut sauce',
        'description' => 'A peanut-based sauce.',
    ]);

    $ingredient->save();

    $meat = Meat::query()->create([
        'extra_price' => '2.50',
    ]);

    $meat->translateOrNew('en')->fill([
        'name' => 'Chicken',
        'description' => 'Grilled chicken.',
    ]);

    $meat->save();

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 2,
        'is_available' => true,
    ]);

    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with peanut curry sauce.',
    ]);

    $dish->save();

    $dish->ingredients()->attach($ingredient);
    $dish->meats()->attach($meat);

    $users = User::factory()->count(2)->create();

    $dish->ratings()->createMany([
        [
            'user_id' => $users[0]->id,
            'rating' => 4,
            'review' => 'Very good.',
        ],
        [
            'user_id' => $users[1]->id,
            'rating' => 5,
            'review' => 'Excellent.',
        ],
    ]);

    $response = $this->getJson(
        "/api/v1/dishes/{$dish->id}",
        ['Accept-Language' => 'en'],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.ingredients.0.id', $ingredient->id)
        ->assertJsonPath('data.ingredients.0.name', 'Peanut sauce')
        ->assertJsonPath(
            'data.ingredients.0.allergen.id',
            $allergen->id,
        )
        ->assertJsonPath(
            'data.ingredients.0.allergen.name',
            'Peanut',
        )
        ->assertJsonPath('data.meats.0.id', $meat->id)
        ->assertJsonPath('data.meats.0.name', 'Chicken')
        ->assertJsonPath('data.meats.0.extra_price', '2.50')
        ->assertJsonPath('data.rating.average', 4.5)
        ->assertJsonPath('data.rating.count', 2);
});
