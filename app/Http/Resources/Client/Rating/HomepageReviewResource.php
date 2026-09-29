<?php

namespace App\Http\Resources\Client\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class HomepageReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'author' => Str::before($this->user->name, ' '),
            'created_at' => $this->created_at?->toISOString(),
            'dish' => [
                'id' => $this->dish->id,
                'name' => $this->dish->name,
            ],
        ];
    }
}
