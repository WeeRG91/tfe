<?php

namespace App\Actions\Client\LoyaltyPointTransaction\Queries;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GetLoyaltyPointTransactions
{
    /**
     * @param int $userId
     * @param string $filter
     * @return CursorPaginator
     */
    public function execute(int $userId, string $filter = 'all'): CursorPaginator
    {
        return LoyaltyPointTransaction::query()
            ->where('user_id', $userId)
            ->when($filter !== 'all', function ($query) use ($filter) {
                match ($filter) {
                    'earned' => $query->where('type', LoyaltyPointTransactionTypeEnum::EARNED),
                    'redeemed' => $query->where('type', LoyaltyPointTransactionTypeEnum::REDEEMED),
                    'refunded' => $query->where('type', LoyaltyPointTransactionTypeEnum::REFUNDED),
                    default => null,
                };
            })
            ->latest()
            ->cursorPaginate(10);
    }
}
