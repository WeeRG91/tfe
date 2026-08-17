<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Fortify\Features;

class AuthenticatedSessionController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function store(LoginRequest $request): JsonResponse
    {
        $user = $request->validateCredentials();

        if (
            Features::enabled(Features::twoFactorAuthentication()) &&
            $user->hasEnabledTwoFactorAuthentication()
        ) {
            return response()->json([
                'message' => 'Two-factor authentication is required.',
                'code' => 'two_factor_required',
            ], 409);
        }

        $token = $user->createToken(
            (string) $request->string('device_name'),
            ['mobile'],
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }
}
