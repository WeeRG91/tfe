<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MobileTwoFactorChallengeStore
{
    private const EXPIRATION_MINUTES = 5;

    public function create(User $user, string $deviceName): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->cacheKey($token),
            [
                'user_id' => $user->getKey(),
                'device_name' => $deviceName,
            ],
            now()->addMinutes(self::EXPIRATION_MINUTES)
        );

        return $token;
    }

    public function find(string $token): ?array
    {
        $challenge = Cache::get($this->cacheKey($token));

        return is_array($challenge) ? $challenge : null;
    }

    public function forget(string $token): void
    {
        Cache::forget($this->cacheKey($token));
    }

    private function cacheKey(string $token): string
    {
        return 'mobile_two_factor_challenge:'
            .hash('sha256', $token);
    }
}
