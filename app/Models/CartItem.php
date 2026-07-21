<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'spicy_level' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function meat(): BelongsTo
    {
        return $this->belongsTo(Meat::class);
    }

    public function removedIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'cart_item_removed_ingredients');
    }
}
