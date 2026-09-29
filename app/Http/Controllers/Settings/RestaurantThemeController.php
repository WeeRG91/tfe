<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Permissions\AdminPermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateRestaurantThemeRequest;
use App\Services\RestaurantThemeService;
use App\Services\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;

class RestaurantThemeController extends Controller
{
    /**
     * @throws JsonException
     */
    public function edit(
        Request $request,
        ThemeRegistry $themes,
        RestaurantThemeService $restaurantThemes,
    ): Response {
        return Inertia::render('settings/Appearance', [
            'availableThemes' => $themes->allPublishedThemes(),
            'defaultClientThemeKey' => $restaurantThemes->defaultClientThemeKey(),
            'managedCustomThemes' => $request->user()?->can(
                AdminPermissionEnum::THEME_MANAGE->value,
            ) === true
                ? $themes->allCustomThemes()
                : [],
        ]);
    }

    /**
     * @throws JsonException
     */
    public function updateDefault(
        UpdateRestaurantThemeRequest $request,
        RestaurantThemeService $restaurantThemes,
    ): RedirectResponse {
        $validated = $request->validated();

        $restaurantThemes->setDefaultClientTheme(
            $validated['default_client_theme_key'],
            $request->user(),
        );

        return back();
    }
}
