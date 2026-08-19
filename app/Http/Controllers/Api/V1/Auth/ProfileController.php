<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Notifications\Auth\MobileVerifyEmail;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $validated = $request->validated();
        $user = request()->user();

        $emailChanged = $user->email !== $validated['email'];

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'locale' => $validated['locale'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->notify(new MobileVerifyEmail);
        }

        return new UserResource($user);
    }
}
