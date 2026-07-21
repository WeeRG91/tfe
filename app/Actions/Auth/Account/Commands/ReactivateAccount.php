<?php

namespace App\Actions\Auth\Account\Commands;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactivateAccount
{
    /**
     * @param User $user
     * @param Request $request
     * @return RedirectResponse|void
     */
    public function execute(User $user, Request $request)
    {
        if (!$user->trashed()) {
            return redirect()->route('login');
        }

        $user->restore();

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user);

        $request->session()->regenerate();
    }
}
