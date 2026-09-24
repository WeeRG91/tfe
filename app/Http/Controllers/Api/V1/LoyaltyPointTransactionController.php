<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Client\LoyaltyPointTransaction\Queries\GetLoyaltyPointTransactions;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LoyaltyPointTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoyaltyPointTransactionController extends Controller
{
    public function index(
        Request $request,
        GetLoyaltyPointTransactions $getLoyaltyPointTransactions,
    ): AnonymousResourceCollection {
        $transactions = $getLoyaltyPointTransactions->execute($request->user()->id);

        return LoyaltyPointTransactionResource::collection($transactions);
    }
}
