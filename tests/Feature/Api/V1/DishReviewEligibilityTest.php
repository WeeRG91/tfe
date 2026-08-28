<?php

use App\Enums\DishCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function dishForMobileReviewEligibility(): Dish
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

function completedMobileOrderForReviewEligibility(User $user, Dish $dish): Order
{
    $order = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.Str::ulid(),
        'type' => OrderTypeEnum::TAKEAWAY,
        'status' => OrderStatusEnum::COMPLETED,
        'payment_method' => PaymentMethodEnum::CASH,
        'payment_status' => PaymentStatusEnum::PAID,
        'subtotal' => '14.50',
        'discount_total' => '0.00',
        'vat_total' => '1.55',
        'delivery_fee' => '0.00',
        'total_inc_vat' => '14.50',
        'completed_at' => now(),
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

it('allows an authenticated customer to review a dish from a completed order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewEligibility();

    completedMobileOrderForReviewEligibility($user, $dish);

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/dishes/{$dish->id}/review-eligibility")
        ->assertOk()
        ->assertJsonPath('data.can_review', true)
        ->assertJsonPath('data.review', null);
});

it('does not allow a customer to review a dish without a completed order', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewEligibility();

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/dishes/{$dish->id}/review-eligibility")
        ->assertOk()
        ->assertJsonPath('data.can_review', false)
        ->assertJsonPath('data.review', null);
});

it('returns the authenticated customers existing dish review', function () {
    $user = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewEligibility();

    completedMobileOrderForReviewEligibility($user, $dish);

    $review = $dish->ratings()->create([
        'user_id' => $user->id,
        'rating' => 5,
        'review' => 'Excellent.',
    ]);

    $dish->ratings()->create([
        'user_id' => $otherUser->id,
        'rating' => 2,
        'review' => 'Not mine.',
    ]);

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/dishes/{$dish->id}/review-eligibility")
        ->assertOk()
        ->assertJsonPath('data.can_review', false)
        ->assertJsonPath('data.review.id', $review->id)
        ->assertJsonPath('data.review.rating', 5)
        ->assertJsonPath('data.review.review', 'Excellent.')
        ->assertJsonPath('data.review.user.id', $user->id);
});

it('requires a verified email to view dish review eligibility', function () {
    $user = User::factory()->withoutTwoFactor()->unverified()->create();
    $token = $user->createToken('Test phone', ['mobile']);
    $dish = dishForMobileReviewEligibility();

    $this
        ->withToken($token->plainTextToken)
        ->getJson("/api/v1/dishes/{$dish->id}/review-eligibility")
        ->assertForbidden();
});

it('requires authentication to view dish review eligibility', function () {
    $dish = dishForMobileReviewEligibility();

    $this
        ->getJson("/api/v1/dishes/{$dish->id}/review-eligibility")
        ->assertUnauthorized();
});
