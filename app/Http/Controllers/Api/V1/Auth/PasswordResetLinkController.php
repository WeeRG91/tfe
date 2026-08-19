<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\Auth\MobileResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function store(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user) {
            $token = Password::broker()->createToken($user);

            $user->notify(new MobileResetPassword($token));
        }

        return response()->json([
            'message' => 'If an account exists for this email, a password reset link has been sent.',
        ], 202);
    }
}
