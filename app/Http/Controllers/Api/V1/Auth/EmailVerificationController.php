<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Auth\MobileVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse|Response
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->noContent();
        }

        $user->notify(new MobileVerifyEmail);

        return response()->json([
            'message' => 'Verification link sent.',
        ], 202);
    }

    public function verify(int $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(
            hash_equals(
                $hash,
                sha1(($user->getEmailForVerification())),
            ),
            403
        );

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }
}
