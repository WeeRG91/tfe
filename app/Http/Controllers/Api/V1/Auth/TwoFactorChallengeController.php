<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\TwoFactorChallengeRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\Auth\MobileTwoFactorChallengeStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Fortify;

class TwoFactorChallengeController extends Controller
{
    public function store(
        TwoFactorChallengeRequest $request,
        MobileTwoFactorChallengeStore $challengeStore,
        TwoFactorAuthenticationProvider $provider,
    ): JsonResponse {
        $challengeToken = $request
            ->string('challenge_token')
            ->value();

        $challenge = $challengeStore->find($challengeToken);

        if (! $challenge) {
            throw ValidationException::withMessages([
                'challenge_token' => [
                    'The two-factor challenge is invalid or has expired.',
                ],
            ]);
        }

        $user = User::query()->find($challenge['user_id']);

        if (! $user || ! $user->hasEnabledTwoFactorAuthentication()) {
            $challengeStore->forget($challengeToken);

            throw ValidationException::withMessages([
                'challenge_token' => [
                    'The two-factor challenge is invalid or has expired.',
                ],
            ]);
        }

        $validRecoveryCode = null;

        if ($request->filled('recovery_code')) {
            $submittedRecoveryCode = $request
                ->string('recovery_code')
                ->value();

            /** @var array<int, string> $recoveryCodes */
            $recoveryCodes = $user->recoveryCodes();

            foreach ($recoveryCodes as $recoveryCode) {
                if (hash_equals($recoveryCode, $submittedRecoveryCode)) {
                    $validRecoveryCode = $recoveryCode;

                    break;
                }
            }
        }

        $hasValidCode = $request->filled('code')
            && $provider->verify(
                Fortify::currentEncrypter()->decrypt(
                    $user->two_factor_secret,
                ),
                $request->string('code')->value(),
            );

        if (! $validRecoveryCode && ! $hasValidCode) {
            event(new TwoFactorAuthenticationFailed($user));

            $field = $request->filled('recovery_code')
                ? 'recovery_code'
                : 'code';

            throw ValidationException::withMessages([
                $field => ['The provided two-factor code is invalid.'],
            ]);
        }

        if ($validRecoveryCode) {
            $user->replaceRecoveryCode($validRecoveryCode);
        }

        event(new ValidTwoFactorAuthenticationCodeProvided($user));

        $challengeStore->forget($challengeToken);

        $token = $user->createToken(
            $challenge['device_name'],
            ['mobile'],
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => new UserResource($user),
            ],
        ]);
    }
}
