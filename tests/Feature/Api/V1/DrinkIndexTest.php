<?php

use App\Enums\DrinkCategoryEnum;
use App\Models\Drink;
use Illuminate\Support\Facades\Storage;

it('returns the public drink menu as json', function () {
    $drink = Drink::query()->create([
        'price' => '4.50',
        'category' => DrinkCategoryEnum::SMOOTHIE,
        'is_available' => true,
        'main_image' => 'drinks/mango-smoothie.jpg',
    ]);

    $drink->translateOrNew('en')->fill([
        'name' => 'Mango smoothie',
        'description' => 'Fresh mango blended with ice.',
    ]);

    $drink->save();

    $response = $this->getJson('/api/v1/drinks', [
        'Accept-Language' => 'en',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $drink->id)
        ->assertJsonPath('data.0.name', 'Mango smoothie')
        ->assertJsonPath(
            'data.0.description',
            'Fresh mango blended with ice.',
        )
        ->assertJsonPath('data.0.price', '4.50')
        ->assertJsonPath(
            'data.0.image_url',
            Storage::disk('public')->url(
                'drinks/mango-smoothie.jpg',
            ),
        )
        ->assertJsonPath('data.0.is_available', true)
        ->assertJsonPath('data.0.category.value', 3)
        ->assertJsonPath('data.0.category.key', 'smoothie');
});

it('does not return soft-deleted drinks', function () {
    $drink = Drink::query()->create([
        'price' => '3.00',
        'category' => DrinkCategoryEnum::SOFT_DRINK,
        'is_available' => true,
    ]);

    $drink->delete();

    $this->getJson('/api/v1/drinks')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
