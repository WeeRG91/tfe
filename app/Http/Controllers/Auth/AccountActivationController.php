<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivateAccountRequest;
use App\Models\User;
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

        return Inertia::render('auth/Activate', [
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
        return Inertia::render('auth/ActivationExpired');
    }

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $user = User::where('email', $validated['email'])
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                return back()->with('message', 'User not found. The account may be already activated or not be created. Please try to login or sign up again.');
            }

            $url = URL::temporarySignedRoute(
                'activate.show',
                now()->addDays(3),
                [
                    'user' => $user->id,
                ]
            );

            $user->sendEmailVerificationNotification();

            return back()->with(
                'message',
                'If this email belongs to an inactive account, a new activation link has been sent.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }
}
