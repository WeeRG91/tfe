<?php

namespace App\Models;

use App\Enums\DrinkCategoryEnum;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drink extends Model
{
    use SoftDeletes, HasImages;

    protected string $folder = 'images/drink';
    protected string $imageInput = 'images';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => DrinkCategoryEnum::class,
        ];
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
