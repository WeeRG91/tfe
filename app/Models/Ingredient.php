<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Ingredient extends Model
{
    protected $guarded = ['id'];

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
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
