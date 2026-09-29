<?php

namespace App\Services;

use App\Models\CustomTheme;
use App\Models\RestaurantThemeSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;
use Throwable;

readonly class CustomThemeService
{
    public function __construct(
        private ThemeRegistry $themes,
    ) {}

    /**
     * @throws JsonException
     */
    public function createDraft(
        string $name,
        string $baseThemeKey,
        User $actor,
    ): CustomTheme {
        $name = trim($name);

        Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'max:100']],
        )->validate();

        $base = $this->themes->findBuiltInTheme($baseThemeKey);

        if ($base === null || ($base['published'] ?? false) !== true) {
            throw ValidationException::withMessages([
                'base_theme_key' => 'The selected base theme is unavailable.',
            ]);
        }

        return CustomTheme::query()->create([
            'key' => 'custom/'.Str::uuid(),
            'name' => $name,
            'mode' => $base['mode'],
            'colors' => $base['colors'],
            'radius' => $base['radius'],
            'published' => false,
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ]);
    }

    /**
     * @throws JsonException
     */
    public function updateTheme(
        CustomTheme $theme,
        array $input,
        User $actor,
    ): CustomTheme {
        $name = $input['name'] ?? null;

        if (is_string($name)) {
            $name = trim($name);
        }

        $validated = Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'max:100']],
        )->validate();

        $colors = $this->validatePalette($input['colors'] ?? null);

        if ($theme->published) {
            $this->assertReadablePalette($colors);
        }

        $theme->update([
            'name' => $validated['name'],
            'colors' => $colors,
            'updated_by' => $actor->getKey(),
        ]);

        return $theme->refresh();
    }

    /**
     * @throws JsonException
     */
    public function publishTheme(
        CustomTheme $theme,
        User $actor,
    ): CustomTheme {
        $colors = $this->validatePalette($theme->colors);
        $this->assertReadablePalette($colors);

        $theme->update([
            'published' => true,
            'updated_by' => $actor->getKey(),
        ]);

        return $theme->refresh();
    }

    /**
     * @throws Throwable
     */
    public function deleteTheme(
        CustomTheme $theme,
        User $actor,
    ): void {
        DB::transaction(function () use ($theme, $actor): void {
            RestaurantThemeSetting::query()
                ->where('default_client_theme_key', $theme->key)
                ->update([
                    'default_client_theme_key' => 'builtin/light',
                    'updated_by' => $actor->getKey(),
                ]);

            $theme->update(['updated_by' => $actor->getKey()]);
            $theme->delete();
        });
    }

    /**
     * @return array<string, string>
     *
     * @throws JsonException
     */
    private function validatePalette(mixed $colors): array
    {
        $colorKeys = array_keys($this->themes->fallbackTheme()['colors']);

        $validated = Validator::make(['colors' => $colors], [
            'colors' => [
                'required',
                'array:'.implode(',', $colorKeys),
                'size:'.count($colorKeys),
            ],
            'colors.*' => [
                'required',
                'string',
                'regex:/\A#[0-9A-Fa-f]{6}\z/',
            ],
        ])->validate();

        return $validated['colors'];
    }

    private function assertReadablePalette(array $colors): void
    {
        $errors = [];

        foreach (ThemeContrast::TEXT_PAIRS as [$background, $foreground]) {
            $radio = ThemeContrast::ratio(
                $colors[$background],
                $colors[$foreground],
            );

            if ($radio < 4.5) {
                $errors["colors.$foreground"] =
                    "Contrast between $foreground and $background must be at least 4.5:1.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
