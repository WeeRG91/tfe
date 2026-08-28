<?php

use App\Enums\DishCategoryEnum;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function dishForMobileReviews(): Dish
{
    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);

    $dish->translateOrNew('en')->fill([
        'name' => 'Chicken curry',
        'description' => 'Chicken with curry sauce.',
    ]);

    $dish->save();

    return $dish;
}

it('returns public dish reviews newest first with pagination', function () {
    $dish = dishForMobileReviews();
    $olderReviewer = User::factory()->withoutTwoFactor()->create([
        'name' => 'Older Customer',
    ]);
    $newerReviewer = User::factory()->withoutTwoFactor()->create([
        'name' => 'Newer Customer',
    ]);

    $avatar = $newerReviewer->images()->create([
        'name' => 'avatar.jpg',
        'path' => 'images/user/avatar.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
    ]);

    $olderReview = $dish->ratings()->create([
        'user_id' => $olderReviewer->id,
        'rating' => 4,
        'review' => 'Very good.',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $newerReview = $dish->ratings()->create([
        'user_id' => $newerReviewer->id,
        'rating' => 5,
        'review' => 'Excellent.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this
        ->getJson("/api/v1/dishes/{$dish->id}/reviews?per_page=1")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $newerReview->id)
        ->assertJsonPath('data.0.rating', 5)
        ->assertJsonPath('data.0.review', 'Excellent.')
        ->assertJsonPath('data.0.user.id', $newerReviewer->id)
        ->assertJsonPath('data.0.user.name', 'Newer Customer')
        ->assertJsonPath(
            'data.0.user.avatar_url',
            Storage::disk('public')->url($avatar->path),
        )
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('links.prev', null)
        ->assertJsonPath(
            'data.0.created_at',
            $newerReview->created_at->toISOString(),
        )
        ->assertJsonMissing(['id' => $olderReview->id]);
});

it('returns an empty paginated review list for a dish without reviews', function () {
    $dish = dishForMobileReviews();

    $this
        ->getJson("/api/v1/dishes/{$dish->id}/reviews")
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 0);
});

it('returns not found when listing reviews for a missing dish', function () {
    $this
        ->getJson('/api/v1/dishes/999999/reviews')
        ->assertNotFound();
});
