<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Order\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\PlaceOrderRequest;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Throwable;

class OrderController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getOrders()
    {
        $orders = Order::query()->where('user_id', auth()->id())->get();
        $orders->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return response()->json(OrderResource::collection($orders)->collection);
    }

    public function getOrder(int $orderId)
    {
        $order = Order::query()->findOrFail($orderId);
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * @param PlaceOrderRequest $request
     * @param PlaceOrder $placeOrder
     * @return JsonResponse
     * @throws Throwable
     */
    public function placeOrder(PlaceOrderRequest $request, PlaceOrder $placeOrder)
    {
        $result = $placeOrder->execute($request->validated());

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => new OrderResource($result['order']),
        ]);
    }
}
