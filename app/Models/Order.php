<?php

namespace App\Models;

use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => OrderTypeEnum::class,
        'status' => OrderStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'payment_status' => PaymentStatusEnum::class,
        'pickup_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'prepare_at' => 'datetime',
        'ready_at' => 'datetime',
        'vat_breakdown' => 'array',
        'delivery_type' => DeliveryTypeEnum::class,
        'delivery_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function deliveryCompany(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class);
    }
}
