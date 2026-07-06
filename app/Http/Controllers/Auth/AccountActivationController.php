<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivateAccountRequest;
use App\Models\User;
use App\Notifications\AccountActivationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Throwable;

class AccountActivationController extends Controller
{
    public function show(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/account-activation/Activate', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function store(ActivateAccountRequest $request, User $user)
    {
        try {
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->password = Hash::make($request->password);

            $user->save();

            Auth::login($user);

            return redirect()->route('home');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function expired()
    {
        return Inertia::render('auth/account-activation/Expired');
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
                return back()->withErrors(['error' => 'User already activated. Please try to login your account.']);
            }

            $url = URL::temporarySignedRoute(
                'activate.show',
                now()->addDay(),
                [
                    'user' => $user->id,
                ]
            );

            $user->notify(new AccountActivationNotification($url));

            return back()->with(
                'message',
                'A new activation link has been sent.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }
}
