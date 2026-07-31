<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes, Translatable;

    public array $translatedAttributes = [
        'name',
        'description',
    ];

    public bool $useTranslationFallback = true;

    protected $guarded = ['id'];

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function mainImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_ingredients');
    }

    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }

    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(
            CartItem::class,
            'cart_item_removed_ingredients'
        );
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(
            OrderItem::class,
            'order_item_removed_ingredients'
        );
    }
}
