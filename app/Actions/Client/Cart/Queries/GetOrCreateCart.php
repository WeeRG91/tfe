<?php

namespace App\Actions\Client\Cart\Queries;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GetOrCreateCart
{
    public function execute(Request $request): Cart
    {
        $guestToken = $request->header('X-Guest-Token');

        if ($request->user()) {
            $userId = $request->user()->id;
            $existingUserCart = Cart::query()->where('user_id', $userId)->first();

            if ($existingUserCart) {
                return $existingUserCart;
            }

            if ($guestToken) {
                $existingGuestCart = Cart::query()->where('guest_token', $guestToken)->first();

                if ($existingGuestCart) {
                    $existingGuestCart->update([
                        'user_id' => $userId,
                        'guest_token' => null,
                    ]);

                    return $existingGuestCart;
                }
            }

            return Cart::query()->create([
                'user_id' => $userId,
            ]);
        }

        if (! $guestToken) {
            $guestToken = Str::uuid()->toString();
        }

        return Cart::query()->firstOrCreate(['guest_token' => $guestToken]);
    }
}
