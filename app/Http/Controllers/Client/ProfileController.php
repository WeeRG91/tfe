<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('client/MyProfile', [
            'twoFactorAuthEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        ]);
    }
}
