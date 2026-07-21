<?php

namespace App\Http\Controllers\Client;

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
    /**
     * @param Dish $dish
     * @return JsonResponse
     */
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

    /**
     * @param Request $request
     * @param Dish $dish
     * @return JsonResponse
     */
    public function store(Request $request, Dish $dish): JsonResponse
    {
        $existingRating = DishRating::query()
            ->where('dish_id', $dish->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'You have already rated this dish.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string'],
        ]);

        $rating = DishRating::query()->create([
                'dish_id' => $dish->id,
                'user_id' => $request->user()->id,
                'rating' => $validated['rating'],
                'review' => $validated['review'],
            ]
        );

        $rating->load('user');

        event(new DishRatingUpdatedBroadcast($dish, $rating));

        return response()->json([
            'success' => true,
        ]);
    }

    public function update(Request $request, DishRating $rating): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string'],
        ]);

        if ($rating->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You can only edit your own rating.',
            ], 403);
        }

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
        if ($rating->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You can only delete your own rating.',
            ], 403);
        }

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
