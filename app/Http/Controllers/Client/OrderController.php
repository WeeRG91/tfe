<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Order\Commands\PlaceOrder\PlaceOrder;
use App\Actions\Client\Order\Commands\Reorder\Reorder;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\PlaceOrderRequest;
use App\Http\Requests\Client\Order\ReorderRequest;
use App\Http\Resources\Client\Address\AddressResource;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class OrderController extends Controller
{
    /**
     * @param Order $order
     * @return InertiaResponse
     */
    public function orderDetails(Order $order): InertiaResponse
    {
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        return Inertia::render('client/OrderDetails', [
            'orderToShow' => new OrderResource($order),
        ]);
    }

    /**
     * @return InertiaResponse
     */
    public function myOrders(): InertiaResponse
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
     * @param Order $order
     * @return JsonResponse
     */
    public function getOrder(Order $order): JsonResponse
    {
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
     * @param Order $order
     * @return InertiaResponse
     */
    public function reorder(Order $order): InertiaResponse
    {
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients');

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

    /**
     * @param ReorderRequest $request
     * @param Reorder $reorder
     * @return JsonResponse
     * @throws Throwable
     */
    public function confirmReorder(ReorderRequest $request, Reorder $reorder): JsonResponse
    {
        $result = $reorder->execute($request->validated());

        return response()->json([
            'message' => $result['message'],
            'order' => new OrderResource($result['order']),
        ]);
    }

    /**
     * @param Order $order
     * @return HttpResponse
     */
    public function cancel(Order $order): HttpResponse
    {
        $order->update([
            'status' => OrderStatusEnum::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        return response()->noContent();
    }

    /**
     * @param Order $order
     * @return HttpResponse
     */
    public function destroy(Order $order): HttpResponse
    {
        $order->delete();

        return response()->noContent();
    }
}
