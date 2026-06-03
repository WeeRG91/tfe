<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Order\Commands\PlaceOrder\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\PlaceOrderRequest;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class OrderController extends Controller
{
    public function orderDetails(int $orderId)
    {
        $order = Order::query()->findOrFail($orderId);
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return Inertia::render('client/OrderDetails', [
            'orderToShow' => new OrderResource($order),
        ]);
    }

    public function myOrders()
    {
        return Inertia::render('client/MyOrders');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrders(Request $request): JsonResponse
    {
        $query = Order::query()
            ->where('user_id', auth()->id())
            ->when($request->status != 0, function ($q) use ($request) {
                if (in_array($request->status, [6, 7])) {
                    $q->where('status', $request->status);
                } else {
                    $q->whereNotIn('status', [6, 7]);
                }
            });

        $orders = $query
            ->with([
                'user',
                'items.item',
                'items.meat',
                'items.removedIngredients',
                'address'
            ])
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json(OrderResource::collection($orders)->collection);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function getOrder(int $orderId): JsonResponse
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
    public function placeOrder(PlaceOrderRequest $request, PlaceOrder $placeOrder): JsonResponse
    {
        $result = $placeOrder->execute($request->validated());

        return response()->json([
            'message' => $result['message'],
            'order' => new OrderResource($result['order']),
        ]);
    }
}
