<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];
}
