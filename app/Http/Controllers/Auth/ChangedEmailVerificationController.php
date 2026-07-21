<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangedEmailVerification\Commands\ResendChangedEmailVerificationLink;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class ChangedEmailVerificationController extends Controller
{
    public function store(User $user)
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('login');
    }

    public function expired()
    {
        return Inertia::render('auth/changed-email-verification/Expired');
    }

    public function resend(Request $request, ResendChangedEmailVerificationLink $resendChangedEmailVerificationLink)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        try {
            $user = User::where('email', $validated['email'])
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                return back()->withErrors(['error' => 'User not found. Please try to sign up to create an account.']);
            }

            if ($user->hasVerifiedEmail()) {
                return back()->withErrors(['error' => 'Email already verified. Please try to login your account.']);
            }

            $resendChangedEmailVerificationLink->execute($user);

            return back()->with(
                'message',
                'A new verification link has been sent.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }
}
