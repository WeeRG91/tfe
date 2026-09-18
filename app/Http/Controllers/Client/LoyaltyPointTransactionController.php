<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\LoyaltyPointTransaction\Queries\GetLoyaltyPointTransactions;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\LoyaltyPointTransaction\LoyaltyPointTransactionResource;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LoyaltyPointTransactionController extends Controller
{
    public function myPoints(): InertiaResponse
    {
        $availablePoints = LoyaltyPointTransaction::query()
            ->with('order:id,order_number')
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('client/MyPoints', [
            'availablePoints' => LoyaltyPointTransactionResource::collection($availablePoints)->collection,
        ]);
    }

    public function getLoyaltyPointTransactions(
        Request $request,
        GetLoyaltyPointTransactions $getLoyaltyPointTransactions
    ): JsonResponse {
        $transactions = $getLoyaltyPointTransactions->execute(
            auth()->id(),
            $request->input('filter', 'all')
        );

        return response()->json([
            'data' => LoyaltyPointTransactionResource::collection($transactions)->collection,
            'path' => $transactions->path(),
            'per_page' => $transactions->perPage(),
            'next_cursor' => $transactions->nextCursor()?->encode(),
            'next_page_url' => $transactions->nextPageUrl(),
            'prev_cursor' => $transactions->previousCursor()?->encode(),
            'prev_page_url' => $transactions->previousPageUrl(),
        ]);
    }
}
