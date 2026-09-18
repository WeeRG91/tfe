<?php

namespace App\Actions\Auth\Account\Commands;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ActivateAccount
{
    public function execute(
        User $user,
        string $password,
        Request $request
    ): void {
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        Auth::login($user);

        $request->session()->regenerate();
    }
}
