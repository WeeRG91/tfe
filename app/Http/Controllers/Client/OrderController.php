<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Order\Commands\CancelOrder;
use App\Actions\Client\Order\Commands\DeleteOrder;
use App\Actions\Client\Order\Commands\PlaceOrder\PlaceOrder;
use App\Actions\Client\Order\Commands\Reorder\Reorder;
use App\Actions\Client\Order\Queries\GetOrder;
use App\Actions\Client\Order\Queries\GetOrders;
use App\Actions\Client\Order\Queries\GetReorderData;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\PlaceOrderRequest;
use App\Http\Requests\Client\Order\ReorderRequest;
use App\Http\Resources\Client\Address\AddressResource;
use App\Http\Resources\Client\Order\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class OrderController extends Controller
{
    /**
     * @param Order $order
     * @param GetOrder $getOrder
     * @return InertiaResponse
     */
    public function orderDetails(Order $order, GetOrder $getOrder): InertiaResponse
    {
        $this->authorize('view', $order);

        return Inertia::render('client/OrderDetails', [
            'orderToShow' => new OrderResource(
                $getOrder->execute($order)
            ),
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
     * @param GetOrders $getOrders
     * @return JsonResponse
     */
    public function getOrders(Request $request, GetOrders $getOrders): JsonResponse
    {
        $orders = $getOrders->execute(
            auth()->user(),
            $request->input('status'),
        );

        return response()->json(OrderResource::collection($orders)->collection);
    }

    /**
     * @param Order $order
     * @param GetOrder $getOrder
     * @return JsonResponse
     */
    public function getOrder(Order $order, GetOrder $getOrder): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'order' => new OrderResource(
                $getOrder->execute($order)
            ),
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
        $results = $placeOrder->execute($request->validated());

        return response()->json([
            'message' => $results['message'],
            'order' => new OrderResource($results['order']),
        ]);
    }

    /**
     * @param Order $order
     * @param GetReorderData $getReorderData
     * @return InertiaResponse
     */
    public function reorder(Order $order, GetReorderData $getReorderData): InertiaResponse
    {
        $result = $getReorderData->execute(
            auth()->user(),
            $order,
        );

        return Inertia::render('client/Reorder', [
            'orderToReorder' => new OrderResource($result['order']),
            'orderTypes' => OrderTypeEnum::getTypes(),
            'paymentMethods' => PaymentMethodEnum::getPaymentMethods(),
            'addresses' => AddressResource::collection($result['addresses'])->collection,
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
     * @param CancelOrder $cancelOrder
     * @return JsonResponse
     * @throws Throwable
     */
    public function cancel(Order $order, CancelOrder $cancelOrder): JsonResponse
    {
        $this->authorize('cancel', $order);

        $cancelOrder->execute($order);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @param Order $order
     * @param DeleteOrder $deleteOrder
     * @return JsonResponse
     */
    public function destroy(Order $order, DeleteOrder $deleteOrder): JsonResponse
    {
        $this->authorize('delete', $order);

        $deleteOrder->execute($order);

        return response()->json([
            'success' => true,
        ]);
    }
}
