<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'integer', Rule::in([1, 7, 30, 90])],
        ]);

        $period = (int) ($validated['period'] ?? 7);

        $restaurantTimezone = config('restaurant.timezone');

        $end = now();
        $localStart = $end->copy()
            ->setTimezone($restaurantTimezone)
            ->subDays($period - 1)
            ->startOfDay();

        $start = $localStart->copy()->utc();

        $completedOrders = Order::query()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->whereBetween('completed_at', [$start, $end])
            ->count();

        $paidCompletedOrdersQuery = Order::query()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->where('orders.payment_status', PaymentStatusEnum::PAID->value)
            ->whereBetween('completed_at', [$start, $end]);

        $paidCompletedSales = (clone $paidCompletedOrdersQuery)
            ->sum('total_inc_vat');

        $paidCompletedOrders = (clone $paidCompletedOrdersQuery)
            ->count();

        $averageOrderValue = $paidCompletedOrders > 0
            ? round((float) $paidCompletedSales / $paidCompletedOrders, 2)
            : 0;

        $salesByDate = [];

        foreach (
            (clone $paidCompletedOrdersQuery)
                ->select(['completed_at', 'total_inc_vat'])
                ->cursor() as $order
        ) {
            $date = $order->completed_at
                ->copy()
                ->setTimezone($restaurantTimezone)
                ->toDateString();

            $salesByDate[$date] =
                ($salesByDate[$date] ?? 0) + (float) $order->total_inc_vat;
        }

        $dailySales = [];

        for ($day = 0; $day < $period; $day++) {
            $date = $localStart->copy()->addDays($day)->toDateString();

            $dailySales[] = [
                'date' => $date,
                'sales' => round($salesByDate[$date] ?? 0, 2),
            ];
        }

        $periodOrdersQuery = Order::query()
            ->whereBetween('created_at', [$start, $end]);

        $totalPeriodOrders = (clone $periodOrdersQuery)->count();

        $hourlyOrderCounts = array_fill(0, 24, 0);

        foreach (
            (clone $periodOrdersQuery)
                ->select('created_at')
                ->cursor() as $order
        ) {
            $localHour = $order->created_at
                ->copy()
                ->setTimezone($restaurantTimezone)
                ->hour;

            $hourlyOrderCounts[$localHour]++;
        }

        $ordersByHour = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $ordersByHour[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => $hourlyOrderCounts[$hour],
            ];
        }

        $orderCountsByType = (clone $periodOrdersQuery)
            ->select('type')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('type')
            ->pluck('order_count', 'type');

        $ordersByType = array_map(
            fn (OrderTypeEnum $type) => [
                'key' => $type->key(),
                'count' => (int) $orderCountsByType->get($type->value, 0),
            ],
            OrderTypeEnum::cases(),
        );

        $cancelledPeriodOrders = (clone $periodOrdersQuery)
            ->where('status', OrderStatusEnum::CANCELLED->value)
            ->count();

        $cancellationRate = $totalPeriodOrders > 0
            ? round($cancelledPeriodOrders / $totalPeriodOrders * 100, 2)
            : 0;

        $topDishCounts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.item_type', Dish::class)
            ->where('orders.status', OrderStatusEnum::COMPLETED->value)
            ->where('orders.payment_status', PaymentStatusEnum::PAID->value)
            ->whereBetween('orders.completed_at', [$start, $end])
            ->select('order_items.item_id')
            ->selectRaw('SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.item_id')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $dishes = Dish::query()
            ->withTrashed()
            ->with('translations')
            ->whereIn('id', $topDishCounts->pluck('item_id'))
            ->get()
            ->keyBy('id');

        $topDishes = $topDishCounts
            ->map(fn ($row) => [
                'name' => $dishes->get($row->item_id)?->name
                    ?? "Dish #{$row->item_id}",
                'quantity' => (int) $row->quantity,
            ])
            ->values()
            ->all();

        $totalPreparationSeconds = 0;
        $preparationSampleSize = 0;

        $timedOrders = Order::query()
            ->where('status', OrderStatusEnum::COMPLETED->value)
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('prepare_at')
            ->whereNotNull('ready_at')
            ->select(['prepare_at', 'ready_at'])
            ->cursor();

        foreach ($timedOrders as $order) {
            $seconds = $order->prepare_at->diffInSeconds($order->ready_at);

            if ($seconds < 0) {
                continue;
            }

            $totalPreparationSeconds += $seconds;
            $preparationSampleSize++;
        }

        $averagePreparationMinutes = $preparationSampleSize > 0
            ? round($totalPreparationSeconds / $preparationSampleSize / 60, 1)
            : null;

        $payingCustomersInPeriod = (clone $paidCompletedOrdersQuery)
            ->distinct()
            ->count('user_id');

        $returningPayingCustomers = DB::table('orders as current_orders')
            ->where('current_orders.status', OrderStatusEnum::COMPLETED->value)
            ->where('current_orders.payment_status', PaymentStatusEnum::PAID->value)
            ->whereBetween('current_orders.completed_at', [$start, $end])
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('orders as previous_orders')
                    ->whereColumn(
                        'previous_orders.user_id',
                        'current_orders.user_id',
                    )
                    ->where('previous_orders.status', OrderStatusEnum::COMPLETED->value)
                    ->where('previous_orders.payment_status', PaymentStatusEnum::PAID->value)
                    ->whereColumn(
                        'previous_orders.completed_at',
                        '<',
                        'current_orders.completed_at'
                    );
            })
            ->distinct()
            ->count('current_orders.user_id');

        $returningCustomerRate = $payingCustomersInPeriod > 0
            ? round($returningPayingCustomers / $payingCustomersInPeriod * 100, 1)
            : null;

        return Inertia::render('Dashboard', [
            'completedOrders' => $completedOrders,
            'paidCompletedSales' => (float) $paidCompletedSales,
            'averageOrderValue' => $averageOrderValue,
            'dailySales' => $dailySales,
            'period' => $period,
            'cancelledPeriodOrders' => $cancelledPeriodOrders,
            'totalPeriodOrders' => $totalPeriodOrders,
            'cancellationRate' => $cancellationRate,
            'topDishes' => $topDishes,
            'ordersByType' => $ordersByType,
            'ordersByHour' => $ordersByHour,
            'averagePreparationMinutes' => $averagePreparationMinutes,
            'preparationSampleSize' => $preparationSampleSize,
            'payingCustomersInPeriod' => $payingCustomersInPeriod,
            'returningPayingCustomers' => $returningPayingCustomers,
            'returningCustomerRate' => $returningCustomerRate,
        ]);
    }
}
