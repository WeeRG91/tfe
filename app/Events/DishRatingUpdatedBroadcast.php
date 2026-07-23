<?php

namespace App\Events;

use App\Models\Dish;
use App\Models\DishRating;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DishRatingUpdatedBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Dish $dish,
        public ?DishRating $rating = null,
        public ?int $deletedReviewId = null
    )
    {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new channel('dish.rating');
    }

    public function broadcastWith(): array
    {
        return [
            'dish_id' => $this->dish->id,
            'review' => $this->rating ? [
                'id' => $this->rating->id,
                'rating' => $this->rating->rating,
                'review' => $this->rating->review,
                'created_at' => $this->rating->created_at,
                'updated_at' => $this->rating->updated_at,
                'user' => [
                    'id' => $this->rating->user->id,
                    'name' => $this->rating->user->name,
                    'avatar' => $this->rating->user->avatar
                        ? Storage::disk('public')->url($this->rating->user->avatar->path)
                        : null,
                ],
            ] : null,
            'deleted_review_id' => $this->deletedReviewId ?? null,
            'rating_average' => round($this->dish->ratings()->avg('rating') ?? 0, 1),
            'rating_count' => $this->dish->ratings()->count(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dish-rating';
    }
}
