<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Order\Commands\CancelOrder;
use App\Actions\Admin\Order\Commands\UpdateOrderStatus;
use App\Actions\Admin\Order\Queries\GetConfirmedOrders;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Order\ConfirmedOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ConfirmedOrderController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);

        return Inertia::render('admin/order/Index');
    }

    /**
     * @param GetConfirmedOrders $getConfirmedOrders
     * @return JsonResponse
     */
    public function getConfirmedOrders(GetConfirmedOrders $getConfirmedOrders): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $confirmedOrders = $getConfirmedOrders->execute();

        return response()->json(ConfirmedOrderResource::collection($confirmedOrders)->collection);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @param UpdateOrderStatus $updateOrderStatus
     * @return JsonResponse
     */
    public function updateOrderStatus(Request $request, Order $order, UpdateOrderStatus $updateOrderStatus): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'newStatus' => ['required', Rule::in(OrderStatusEnum::values())],
        ]);

        $updateOrderStatus->execute($order, $validated['newStatus']);

        return response()->json([
            'message' => __('messages.orders.status_updated'),
            'success' => true,
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
}
