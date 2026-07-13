<?php

namespace App\Http\Controllers\Client;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\LoyaltyPointTransaction\LoyaltyPointTransactionResource;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LoyaltyPointTransactionController extends Controller
{
    /**
     * @return InertiaResponse
     */
    public function myPoints(): InertiaResponse
    {
        $availablePoints = LoyaltyPointTransaction::query()
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('client/MyPoints', [
            'availablePoints' => LoyaltyPointTransactionResource::collection($availablePoints)->collection,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoyaltyPointTransactions(Request $request): JsonResponse
    {
        $filter = $request->filter;

        $loyaltyPointTransactions = LoyaltyPointTransaction::query()
            ->where('user_id', auth()->user()->id)
            ->when($filter !== 'all', function ($query) use ($filter) {
                match ($filter) {
                    'earned' => $query->where('type', LoyaltyPointTransactionTypeEnum::EARNED),
                    'redeemed' => $query->where('type', LoyaltyPointTransactionTypeEnum::REDEEMED),
                    'refunded' => $query->where('type', LoyaltyPointTransactionTypeEnum::REFUNDED),
                };
            })
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10);

        return response()->json([
            'data' => LoyaltyPointTransactionResource::collection($loyaltyPointTransactions)->collection,
            'path' => $loyaltyPointTransactions->path(),
            'per_page' => $loyaltyPointTransactions->perPage(),
            'next_cursor' => $loyaltyPointTransactions->nextCursor()?->encode(),
            'next_page_url' => $loyaltyPointTransactions->nextPageUrl(),
            'prev_cursor' => $loyaltyPointTransactions->previousCursor()?->encode(),
            'prev_page_url' => $loyaltyPointTransactions->previousPageUrl(),
        ]);
    }
}
