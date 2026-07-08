<?php

namespace App\Http\Responses;

use App\Enums\Permissions\AdminPermissionEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     *@param Request $request
     * @return RedirectResponse|Response
     */
    public function toResponse($request): RedirectResponse|Response
    {
        $user = $request->user();

        return $user->can(AdminPermissionEnum::ADMIN_ACCESS->value)
            ? redirect()->intended(route('admin.gateway', absolute: false))
            : redirect()->intended(route('home'));
    }
}
