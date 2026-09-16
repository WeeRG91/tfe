<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDeliveryDate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }
}
