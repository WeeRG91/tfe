<?php

namespace App\Models;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPointTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => LoyaltyPointTransactionTypeEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
