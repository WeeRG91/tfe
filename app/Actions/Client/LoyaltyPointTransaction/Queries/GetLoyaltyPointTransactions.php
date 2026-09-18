<?php

namespace App\Actions\Client\LoyaltyPointTransaction\Queries;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetLoyaltyPointTransactions
{
    public function execute(int $userId, string $filter = 'all'): CursorPaginator
    {
        return LoyaltyPointTransaction::query()
            ->with('order:id,order_number')
            ->where('user_id', $userId)
            ->when($filter !== 'all', function ($query) use ($filter) {
                match ($filter) {
                    'earned' => $query->where('type', LoyaltyPointTransactionTypeEnum::EARNED),
                    'redeemed' => $query->where('type', LoyaltyPointTransactionTypeEnum::REDEEMED),
                    'refunded' => $query->where('type', LoyaltyPointTransactionTypeEnum::REFUNDED),
                    'reversed' => $query->where('type', LoyaltyPointTransactionTypeEnum::REVERSED),
                    default => null,
                };
            })
            ->latest()
            ->cursorPaginate(10);
    }
}
