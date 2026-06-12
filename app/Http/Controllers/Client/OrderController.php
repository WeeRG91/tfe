<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Order\Commands\PlaceOrder\PlaceOrder;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\PlaceOrderRequest;
use App\Http\Resources\Client\Address\AddressResource;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
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
        $status = $request->status;

        $query = Order::query()
            ->where('user_id', auth()->id())
            ->when($status, function ($q) use ($status) {
                match ($status) {
                    'active' => $q->whereIn('status', OrderStatusEnum::activeStatuses()),
                    'completed' => $q->where('status', OrderStatusEnum::COMPLETED->value),
                    'cancelled' => $q->where('status', OrderStatusEnum::CANCELLED->value),
                    default => null,
                };
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

    /**
     * @param int $orderId
     * @return InertiaResponse
     */
    public function reorder(int $orderId): InertiaResponse
    {
        $order = Order::query()->findOrFail($orderId);

        $addresses = Address::query()
            ->where('user_id', auth()->user()->id)
            ->orderBy('is_default', 'desc')
            ->get();

        return Inertia::render('client/Reorder', [
            'orderToReorder' => new OrderResource($order),
            'orderTypes' => OrderTypeEnum::getTypes(),
            'paymentMethods' => PaymentMethodEnum::getPaymentMethods(),
            'addresses' => AddressResource::collection($addresses)->collection,
        ]);
    }
}
