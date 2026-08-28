<?php

use App\Enums\DishCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\DishRatingUpdatedBroadcast;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

function dishForMobileReviewStore(): Dish
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

function mobileOrderContainingDish(
    User $user,
    Dish $dish,
    OrderStatusEnum $status = OrderStatusEnum::COMPLETED,
): Order {
    $order = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => $status,
        'payment_method' => PaymentMethodEnum::CASH,
        'payment_status' => PaymentStatusEnum::PAID,
        'subtotal' => '14.50',
        'discount_total' => '0.00',
        'vat_total' => '1.55',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '14.50',
        'completed_at' => $status === OrderStatusEnum::COMPLETED
            ? now()
            : null,
    ]);

    $order->items()->create([
        'item_id' => $dish->id,
        'item_type' => Dish::class,
        'quantity' => 1,
        'unit_price' => '14.50',
        'vat_rate' => '12.00',
        'vat_amount' => '1.55',
        'total_inc_vat' => '14.50',
    ]);

    return $order;
}

it('creates a review after the authenticated customer completed an order containing the dish', function () {
    Event::fake([DishRatingUpdatedBroadcast::class]);

    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    mobileOrderContainingDish($user, $dish);

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 5,
            'review' => 'Excellent curry.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.review', 'Excellent curry.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('meta.rating.average', 5)
        ->assertJsonPath('meta.rating.count', 1);

    $this->assertDatabaseHas('dish_ratings', [
        'dish_id' => $dish->id,
        'user_id' => $user->id,
        'rating' => 5,
        'review' => 'Excellent curry.',
    ]);

    Event::assertDispatched(DishRatingUpdatedBroadcast::class);
});

it('rejects a review when the customer has not ordered the dish', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 4,
            'review' => 'Not eligible.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('dish_ratings', 0);
});

it('rejects a review before the order containing the dish is completed', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    mobileOrderContainingDish($user, $dish, OrderStatusEnum::READY);

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 4,
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('dish_ratings', 0);
});

it('does not create a second review for the same customer and dish', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    mobileOrderContainingDish($user, $dish);

    $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 4,
        'review' => 'Original review.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 5,
            'review' => 'Duplicate review.',
        ])
        ->assertConflict();

    $this->assertDatabaseCount('dish_ratings', 1);
});

it('validates mobile dish review fields', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    mobileOrderContainingDish($user, $dish);

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 6,
            'review' => str_repeat('a', 2001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rating', 'review']);
});

it('requires a verified email to review a dish', function () {
    $user = User::factory()->withoutTwoFactor()->unverified()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewStore();

    mobileOrderContainingDish($user, $dish);

    $this
        ->withToken($token->plainTextToken)
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 5,
        ])
        ->assertForbidden();
});

it('requires authentication to review a dish', function () {
    $dish = dishForMobileReviewStore();

    $this
        ->postJson("/api/v1/dishes/{$dish->id}/reviews", [
            'rating' => 5,
        ])
        ->assertUnauthorized();
});
