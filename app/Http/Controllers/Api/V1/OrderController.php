<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(
            1,
            min($request->integer('per_page', 10), 20),
        );

        $orders = $request->user()
            ->orders()
            ->with('address')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, int $orderId): OrderResource
    {
        $order = $request->user()
            ->orders()
            ->with([
                'user',
                'address',
                'items.item',
                'items.meat',
                'items.removedIngredients',
            ])
            ->findOrFail($orderId);

        return new OrderResource($order);
    }
}
