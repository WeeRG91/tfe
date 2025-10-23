<?php

namespace App\Models;

use App\Enums\DishCategoryEnum;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use SoftDeletes, HasImages;

    protected string $folder = 'images/dish';
    protected string $imageInput = 'images';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => DishCategoryEnum::class,
        ];
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
