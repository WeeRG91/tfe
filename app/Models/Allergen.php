<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Allergen extends Model
{
    protected $guarded = ['id'];

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
