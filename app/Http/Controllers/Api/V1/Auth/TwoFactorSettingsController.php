<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ConfirmTwoFactorSetupRequest;
use App\Http\Requests\Api\V1\Auth\StartTwoFactorSetupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Fortify;

class TwoFactorSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'enabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
                'setup_pending' => $request->user()->two_factor_secret !== null
                    && $request->user()->two_factor_confirmed_at === null,
            ],
        ]);
    }

    public function store(
        StartTwoFactorSetupRequest $request,
        EnableTwoFactorAuthentication $enable,
    ): JsonResponse {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 409);
        }

        $enable($user);

        $user->refresh();

        return response()->json([
            'data' => [
                'enabled' => false,
                'setup_pending' => true,
                'secret' => Fortify::currentEncrypter()->decrypt($user->two_factor_secret),
                'otpauth_url' => $user->twoFactorQrCodeUrl(),
            ],
        ]);
    }

    public function confirm(
        ConfirmTwoFactorSetupRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
    ): JsonResponse {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 409);
        }

        if ($user->two_factor_secret === null) {
            return response()->json([
                'message' => 'Two-factor setup has not been started.',
            ], 409);
        }

        $confirm($user, $request->string('code')->value());

        $user->refresh();

        return response()->json([
            'data' => [
                'enabled' => true,
                'setup_pending' => false,
                'recovery_codes' => $user->recoveryCodes(),
            ],
        ]);
    }

    public function destroy(
        StartTwoFactorSetupRequest $request,
        DisableTwoFactorAuthentication $disable,
    ): Response {
        $disable($request->user());

        return response()->noContent();
    }

    public function regenerateRecoveryCodes(
        StartTwoFactorSetupRequest $request,
        GenerateNewRecoveryCodes $generate,
    ) {
        $user = $request->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 409);
        }

        $generate($user);

        $user->refresh();

        return response()->json([
            'data' => [
                'recovery_codes' => $user->recoveryCodes(),
            ],
        ]);
    }
}
