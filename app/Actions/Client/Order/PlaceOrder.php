<?php

namespace App\Actions\Client\Order;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class PlaceOrder
{
    /**
     * @param array $data
     * @return array|string[]
     * @throws Throwable
     */
    public function execute(array $data): array
    {
        $cart = Cart::query()->findOrFail($data['cart_id']);

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'message' => 'Cart is empty'
            ];
        }

        return DB::transaction(function () use ($cart, $data) {

            $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;

            $order = Order::query()->create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . rand(1000, 9999),

                'type' => $data['type'],
                'table_number' => $data['table_number'] ?? null,
                'pickup_time' => $data['pickup_time'] ?? null,
                'pickup_name' => $data['pickup_name'] ?? null,
                'pickup_phone' => $data['pickup_phone'] ?? null,
                'address_id' => $data['address_id'] ?? null,

                'payment_method' => $data['payment_method'],
                'payment_status' => OrderStatusEnum::PENDING->value,

                'status' => $isCash
                    ? OrderStatusEnum::CONFIRMED->value
                    : OrderStatusEnum::PENDING->value,

                'confirmed_at' => $isCash ? now() : null,

                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($cart->items as $item) {
                $orderItem = $order->items()->create([
                    'item_id' => $item->item_id,
                    'item_type' => $item->item_type,
                    'meat_id' => $item->meat_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'notes' => $item->notes,
                ]);

                if ($item->removedIngredients()->exists()) {
                    $orderItem->removedIngredients()->attach(
                        $item->removedIngredients->pluck('id')
                    );
                }

                $total += $item->total_price;
            }

            $order->update(['total_price' => $total]);

            $cart->items()->delete();

            return [
                'order' => $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address'),
            ];
        });
    }
}
