<?php

namespace App\Models;

use App\Enums\DishCategoryEnum;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use SoftDeletes, Translatable;

    public array $translatedAttributes = [
        'name',
        'description',
    ];

    public bool $useTranslationFallback = true;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => DishCategoryEnum::class,
            'default_spicy_level' => 'integer',
        ];
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function mainImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients');
    }

    public function meats(): BelongsToMany
    {
        return $this->belongsToMany(Meat::class, 'dish_meats');
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'item');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(DishRating::class);
    }
}
