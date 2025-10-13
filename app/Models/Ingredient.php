<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;


class Ingredient extends Model
{
    protected $guarded = ['id'];

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable');
    }

    public function dish(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class);
    }

    public function allergen(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class);
    }
}
