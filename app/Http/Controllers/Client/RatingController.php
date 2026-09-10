<?php

namespace App\Http\Controllers\Client;

use App\Enums\OrderStatusEnum;
use App\Events\DishRatingUpdatedBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Rating\ReviewResource;
use App\Models\Dish;
use App\Models\DishRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function getReviews(Dish $dish): JsonResponse
    {
        $reviews = $dish->ratings()
            ->with('user')
            ->when(Auth::check(), function ($query) {
                $query->orderByRaw('user_id = ? DESC', [Auth::id()]);
            })
            ->latest()
            ->get();

        return response()->json(
            ReviewResource::collection($reviews)
        );
    }

    public function store(Request $request, Dish $dish): JsonResponse
    {
        $user = $request->user();

        $hasCompletedOrder = $user->orders()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->whereHas('items', function ($query) use ($dish) {
                $query
                    ->where('item_type', Dish::class)
                    ->where('item_id', $dish->id);
            })
            ->exists();

        abort_unless(
            $hasCompletedOrder,
            403,
            __('messages.ratings.not_eligible'),
        );

        $existingRating = $dish->ratings()
            ->where('user_id', $user->id)
            ->exists();

        if ($existingRating) {
            return response()->json([
                'message' => __('messages.ratings.already_rated'),
            ], 409);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $rating = DishRating::query()->create([
            'dish_id' => $dish->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]
        );

        $rating->load('user');

        event(new DishRatingUpdatedBroadcast($dish, $rating));

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function update(Request $request, DishRating $rating): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($rating->user_id === auth()->id(), 403);

        $rating->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
        ]);

        $rating->load('user');

        $dish = Dish::query()
            ->where('id', $rating->dish_id)
            ->first();

        event(new DishRatingUpdatedBroadcast($dish, $rating));

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroy(DishRating $rating): JsonResponse
    {
        abort_unless($rating->user_id === auth()->id(), 403);

        $dish = $rating->dish;
        $deletedReviewId = $rating->id;

        $rating->delete();

        event(new DishRatingUpdatedBroadcast(
            dish: $dish,
            rating: null,
            deletedReviewId: $deletedReviewId
        ));

        return response()->json([
            'success' => true,
        ]);
    }
}
