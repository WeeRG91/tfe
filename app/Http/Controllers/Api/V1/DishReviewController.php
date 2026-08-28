<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatusEnum;
use App\Events\DishRatingUpdatedBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Review\StoreDishReviewRequest;
use App\Http\Requests\Api\V1\Review\UpdateDishReviewRequest;
use App\Http\Resources\Api\V1\DishReviewResource;
use App\Models\Dish;
use App\Models\DishRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DishReviewController extends Controller
{
    public function index(Request $request, Dish $dish): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 50);

        $reviews = $dish->ratings()
            ->with([
                'user.images' => fn ($query) => $query->latest(),
            ])
            ->latest()
            ->paginate($perPage);

        return DishReviewResource::collection($reviews);
    }

    public function store(
        StoreDishReviewRequest $request,
        Dish $dish,
    ): JsonResponse
    {
        $user = $request->user();

        $hasCompleteOrder = $user->orders()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->whereHas('items', function ($query) use ($dish) {
                $query
                    ->where('item_type', Dish::class)
                    ->where('item_id', $dish->id);
            })
            ->exists();

        abort_unless(
            $hasCompleteOrder,
            403,
            __('messages.ratings.not_eligible'),
        );

        $alreadyReviewed = $dish->ratings()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => __('messages.ratings.already_rated'),
            ], 409);
        }

        $validated = $request->validated();

        $rating = DishRating::query()->create([
            'dish_id' => $dish->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        $rating->load([
            'user.images' => fn ($query) => $query->latest(),
        ]);

        event(new DishRatingUpdatedBroadcast(
            dish: $dish,
            rating: $rating,
        ));

        return (new  DishReviewResource($rating))
            ->additional([
                'meta' => [
                    'rating' => $this->ratingSummary($dish),
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateDishReviewRequest $request,
        Dish $dish,
        int $reviewId,
    ): JsonResponse
    {
        /** @var DishRating $rating */
        $rating = $dish->ratings()
            ->where('id', $reviewId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validated();

        $rating->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        $rating->load([
            'user.images' => fn ($query) => $query->latest(),
        ]);

        event(new DishRatingUpdatedBroadcast(
            dish: $dish,
            rating: $rating,
        ));

        return (new  DishReviewResource($rating))
            ->additional([
                'meta' => [
                    'rating' => $this->ratingSummary($dish),
                ]
            ])
            ->response();
    }

    public function destroy(
        Request $request,
        Dish $dish,
        int $reviewId,
    ): Response
    {
        /** @var DishRating $rating */
        $rating = $dish->ratings()
            ->where('id', $reviewId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $deletedReviewId = $rating->id;

        $rating->delete();

        event(new DishRatingUpdatedBroadcast(
            dish: $dish,
            rating: null,
            deletedReviewId: $deletedReviewId,
        ));

        return response()->noContent();
    }

    public function eligibility(
        Request $request,
        Dish $dish,
    ): JsonResponse
    {
        $user = $request->user();

        /** @var DishRating|null $existingReview */
        $existingReview = $dish->ratings()
            ->with([
                'user.images' => fn ($query) => $query->latest(),
            ])
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'data' => [
                    'can_review' => false,
                    'review' => (new DishReviewResource($existingReview))->resolve($request),
                ],
            ]);
        }

        $hasCompletedOrder = $user->orders()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->whereHas('items', function ($query) use ($dish) {
                $query
                    ->where('item_type', Dish::class)
                    ->where('item_id', $dish->id);
            })
            ->exists();

        return response()->json([
            'data' => [
                'can_review' => $hasCompletedOrder,
                'review' => null,
            ],
        ]);
    }

    /**
     * @return array{average: float, count: int}
     */
    private function ratingSummary(Dish $dish): array
    {
        return [
            'average' => round(
                (float) ($dish->ratings()->avg('rating') ?? 0),
                1,
            ),
            'count' => $dish->ratings()->count(),
        ];
    }
}
