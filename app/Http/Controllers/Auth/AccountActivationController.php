<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\Account\Commands\ActivateAccount;
use App\Actions\Auth\Account\Commands\ReactivateAccount;
use App\Actions\Auth\Account\Commands\ResendReactivationLink;
use App\Actions\Auth\Account\Commands\SendActivationLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivateAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AccountActivationController extends Controller
{
    /**
     * @param User $user
     * @return RedirectResponse|Response
     */
    public function show(User $user): RedirectResponse|Response
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

    /**
     * @param ActivateAccountRequest $request
     * @param User $user
     * @param ActivateAccount $activateAccount
     * @return RedirectResponse
     */
    public function store(
        ActivateAccountRequest $request,
        User $user,
        ActivateAccount $activateAccount
    ): RedirectResponse
    {
        try {
            $activateAccount->execute(
                $user,
                $request->password,
                $request
            );

            return redirect()->route('home');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * @return Response
     */
    public function expiredActivation(): Response
    {
        return Inertia::render('auth/account-activation/Expired');
    }

    /**
     * @param Request $request
     * @param SendActivationLink $sendActivationLink
     * @return RedirectResponse
     */
    public function resendActivation(Request $request, SendActivationLink $sendActivationLink): RedirectResponse
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

           $sendActivationLink->execute($user);

            return back()->with(
                'message',
                'A new activation link has been sent.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @param ReactivateAccount $reactivateAccount
     * @return RedirectResponse
     */
    public function reactivate(
        Request $request,
        User $user,
        ReactivateAccount $reactivateAccount
    ): RedirectResponse
    {
        try {
            $reactivateAccount->execute(
                $user,
                $request
            );

            return redirect()->route('home');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * @return Response
     */
    public function expiredReactivation(): Response
    {
        return Inertia::render('auth/account-reactivation/Expired');
    }

    /**
     * @param Request $request
     * @param ResendReactivationLink $resendReactivationLink
     * @return RedirectResponse
     */
    public function resendReactivation(Request $request, ResendReactivationLink $resendReactivationLink): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        try {
            $user = User::withTrashed()
                ->where('email', $validated['email'])
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                return back()->withErrors(['error' => 'User not found. Please try to sign up to create an account.']);
            }

            $resendReactivationLink->execute($user);

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
