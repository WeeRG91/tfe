<?php

namespace App\Actions\Client\Rating\Queries;

use App\Models\DishRating;
use Illuminate\Database\Eloquent\Collection;

class GetHomepageReviews
{
    public function execute(int $limit = 6): Collection
    {
        return DishRating::query()
            ->whereNotNull('review')
            ->where('review', '<>', '')
            ->where('rating', '>=', 4)
            ->whereHas('dish', function ($query) {
                $query->where('is_available', true);
            })
            ->with([
                'user',
                'dish.translations',
            ])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
