<?php

use App\Enums\DishCategoryEnum;
use App\Events\DishRatingUpdatedBroadcast;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function dishForMobileReviewMutation(string $name = 'Chicken curry'): Dish
{
    $dish = Dish::query()->create([
        'price' => '14.50',
        'category' => DishCategoryEnum::MAIN_COURSE,
        'default_spicy_level' => 1,
        'is_available' => true,
    ]);

    $dish->translateOrNew('en')->fill([
        'name' => $name,
        'description' => 'Dish description.',
    ]);

    $dish->save();

    return $dish;
}

it('updates an owned mobile dish review and returns the updated summary', function () {
    Event::fake([DishRatingUpdatedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 3,
        'review' => 'It was fine.',
    ]);

    $dish->ratings()->create([
        'user_id' => $otherUser->id,
        'rating' => 4,
        'review' => 'Very good.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}", [
            'rating' => 5,
            'review' => 'Excellent after another visit.',
        ])
        ->assertOk()
        ->assertJsonPath('data.id', $review->id)
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.review', 'Excellent after another visit.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('meta.rating.average', 4.5)
        ->assertJsonPath('meta.rating.count', 2);

    $this->assertDatabaseHas('dish_ratings', [
        'id' => $review->id,
        'dish_id' => $dish->id,
        'user_id' => $user->id,
        'rating' => 5,
        'review' => 'Excellent after another visit.',
    ]);

    Event::assertDispatched(DishRatingUpdatedBroadcast::class);
});

it('does not update another customers mobile dish review', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $otherUser->id,
        'rating' => 4,
        'review' => 'Original review.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}", [
            'rating' => 1,
            'review' => 'Changed review.',
        ])
        ->assertNotFound();

    expect($review->refresh()->rating)->toBe(4)
        ->and($review->review)->toBe('Original review.');
});

it('does not update a review through a different dish', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();
    $otherDish = dishForMobileReviewMutation('Beef curry');

    $review = $otherDish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 4,
        'review' => 'Original review.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}", [
            'rating' => 2,
        ])
        ->assertNotFound();

    expect($review->refresh()->rating)->toBe(4);
});

it('validates mobile dish review updates', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 4,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}", [
            'rating' => 0,
            'review' => str_repeat('a', 2001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rating', 'review']);
});

it('deletes an owned mobile dish review', function () {
    Event::fake([DishRatingUpdatedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 5,
        'review' => 'Excellent.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('dish_ratings', [
        'id' => $review->id,
    ]);

    Event::assertDispatched(
        DishRatingUpdatedBroadcast::class,
        fn (DishRatingUpdatedBroadcast $event): bool => $event->dish->is($dish)
            && $event->rating === null
            && $event->deletedReviewId === $review->id,
    );
});

it('does not delete another customers mobile dish review', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $otherUser->id,
        'rating' => 4,
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('dish_ratings', [
        'id' => $review->id,
    ]);
});

it('returns not found for a missing mobile dish review', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewMutation();

    $this
        ->withToken($token->plainTextToken)
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/999999", [
            'rating' => 5,
        ])
        ->assertNotFound();

    $this
        ->withToken($token->plainTextToken)
        ->deleteJson("/api/v1/dishes/{$dish->id}/reviews/999999")
        ->assertNotFound();
});

it('requires authentication to update or delete a mobile dish review', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $dish = dishForMobileReviewMutation();

    $review = $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 4,
    ]);

    $this
        ->patchJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}", [
            'rating' => 5,
        ])
        ->assertUnauthorized();

    $this
        ->deleteJson("/api/v1/dishes/{$dish->id}/reviews/{$review->id}")
        ->assertUnauthorized();
});
