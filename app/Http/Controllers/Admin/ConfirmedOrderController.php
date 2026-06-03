<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Order\Commands\UpdateOrderStatus;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Order\ConfirmedOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ConfirmedOrderController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/order/Index');
    }

    /**
     * @return JsonResponse
     */
    public function getConfirmedOrders(): JsonResponse
    {
        $confirmedOrders = Order::query()
            ->with([
                'user',
                'items.item',
                'items.meat',
                'items.removedIngredients',
                'address'
            ])
            ->whereNotNull('confirmed_at')
            ->get();

        return response()->json(ConfirmedOrderResource::collection($confirmedOrders)->collection);
    }

    /**
     * @param Request $request
     * @param int $orderId
     * @param UpdateOrderStatus $updateOrderStatus
     * @return JsonResponse
     */
    public function updateOrderStatus(Request $request, int $orderId, UpdateOrderStatus $updateOrderStatus): JsonResponse
    {
        $validated = $request->validate([
            'newStatus' => ['required', Rule::in(OrderStatusEnum::values())],
        ]);

        $order = Order::query()->findOrFail($orderId);

        $updateOrderStatus->execute($order, $validated['newStatus']);

        return response()->json([
            'message' => 'Updated order status successfully',
        ]);
    }
}
