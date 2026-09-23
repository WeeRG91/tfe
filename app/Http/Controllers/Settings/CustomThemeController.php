<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PublishCustomThemeRequest;
use App\Http\Requests\Settings\StoreCustomThemeRequest;
use App\Http\Requests\Settings\UpdateCustomThemeRequest;
use App\Models\CustomTheme;
use App\Services\CustomThemeService;
use App\Services\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;
use Throwable;

class CustomThemeController extends Controller
{
    /**
     * @throws JsonException
     */
    public function store(
        StoreCustomThemeRequest $request,
        CustomThemeService $customThemes,
    ): RedirectResponse
    {
        $validated = $request->validated();

        $customThemes->createDraft(
            $validated['name'],
            $validated['base_theme_key'],
            $request->user(),
        );

        return to_route('appearance.edit');
    }

    public function edit(
        CustomTheme $customTheme,
        ThemeRegistry $themes,
    ): Response
    {
        return Inertia::render('settings/CustomThemeEdit', [
            'customTheme' => $themes->managedCustomThemeDefinition($customTheme),
        ]);
    }

    /**
     * @throws JsonException
     */
    public function update(
        UpdateCustomThemeRequest $request,
        CustomTheme $customTheme,
        CustomThemeService $customThemes,
    ): RedirectResponse
    {
        $customThemes->updateTheme(
            $customTheme,
            $request->validated(),
            $request->user(),
        );

        return to_route('appearance.custom-themes.edit', $customTheme);
    }

    /**
     * @throws JsonException
     */
    public function publish(
        PublishCustomThemeRequest $request,
        CustomTheme $customTheme,
        CustomThemeService $customThemes,
    ): RedirectResponse
    {
        $customThemes->publishTheme($customTheme, $request->user());

        return to_route('appearance.custom-themes.edit', $customTheme);
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        Request $request,
        CustomTheme $customTheme,
        CustomThemeService $customThemes,
    ): RedirectResponse
    {
        $customThemes->deleteTheme($customTheme, $request->user());

        return to_route('appearance.edit');
    }
}
