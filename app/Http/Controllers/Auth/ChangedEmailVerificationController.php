<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ChangedEmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

    public function resend(Request $request)
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

            $url = URL::temporarySignedRoute(
                'verify-changed-email.store',
                now()->addDay(),
                [
                    'user' => $user->id,
                ]
            );

            $user->notify(new ChangedEmailVerificationNotification($url));

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
