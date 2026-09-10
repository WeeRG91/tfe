<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\LoyaltyPointTransaction;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatar = $this->avatar;

        $loyaltyPointsBalance = (int) $this
            ->loyaltyPointTransactions()
            ->get()
            ->sum(
                fn (LoyaltyPointTransaction $transaction): int => match ($transaction->type) {
                    LoyaltyPointTransactionTypeEnum::EARNED,
                    LoyaltyPointTransactionTypeEnum::REFUNDED => $transaction->points,

                    LoyaltyPointTransactionTypeEnum::REDEEMED,
                    LoyaltyPointTransactionTypeEnum::REVERSED => -$transaction->points,
                }
            );


        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'locale' => $this->locale,
            'email_verified' => $this->email_verified_at !== null,
            'avatar_url' => $avatar
                ? Storage::disk('public')->url($avatar->path)
                : null,
            'loyalty_points_balance' => $loyaltyPointsBalance,
        ];
    }
}
