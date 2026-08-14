<?php

use App\Enums\DishCategoryEnum;
use App\Models\Dish;

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
