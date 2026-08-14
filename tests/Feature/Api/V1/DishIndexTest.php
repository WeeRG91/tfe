<?php

use App\Enums\DishCategoryEnum;
use App\Models\Dish;

it('returns the public dish menu as json', function () {
    app()->setLocale('en');

    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 2,
        'is_available' => true,
    ]);

    $translation = $dish->translateOrNew('en');

    $translation->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with vegetables and curry sauce.',
    ]);

    $dish->save();

    $response = $this->getJson('/api/v1/dishes', [
        'Accept-Language' => 'en',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $dish->id)
        ->assertJsonPath('data.0.name', 'Chicken curry')
        ->assertJsonPath(
            'data.0.description',
            'Chicken with vegetables and curry sauce.',
        )
        ->assertJsonPath('data.0.price', '14.50')
        ->assertJsonPath('data.0.is_available', true)
        ->assertJsonPath('data.0.category.value', 2)
        ->assertJsonPath('data.0.category.key', 'mainCourse')
        ->assertJsonPath('data.0.default_spicy_level', 2);
});

it('does not return soft-deleted dishes', function () {
    $dish = Dish::query()->create([
        'price' => '10.00',
        'category' => DishCategoryEnum::APPETIZER,
        'default_spicy_level' => 0,
        'is_available' => true,
    ]);

    $dish->delete();

    $response = $this->getJson('/api/v1/dishes');

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('uses the accept-language header for dish translations', function () {
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

    $dish->translateOrNew('fr')->fill([
        'name' => 'Curry de poulet',
        'description' => 'Poulet aux légumes et sauce curry.',
    ]);

    $dish->save();

    app()->setLocale('en');

    $response = $this->getJson('/api/v1/dishes', [
        'Accept-Language' => 'fr',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Curry de poulet')
        ->assertJsonPath(
            'data.0.description',
            'Poulet aux légumes et sauce curry.',
        );
});

it('falls back when the requested language is unsupported', function () {
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

    $dish->translateOrNew('fr')->fill([
        'name' => 'Curry de poulet',
        'description' => 'Poulet aux légumes et sauce curry.',
    ]);

    $dish->save();

    app()->setLocale('fr');

    $response = $this->getJson('/api/v1/dishes', [
        'Accept-Language' => 'de',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Chicken curry');
});
