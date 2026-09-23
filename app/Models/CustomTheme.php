<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomTheme extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'name',
        'mode',
        'colors',
        'radius',
        'published',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'published' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
