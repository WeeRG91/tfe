<?php

namespace App\Http\Resources\Api\V1;

use App\Models\DishRating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin DishRating */
class DishReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatar = $this->user->images->first();

        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $avatar
                    ? Storage::disk('public')->url($avatar->path)
                    : null,
            ],
        ];
    }
}
