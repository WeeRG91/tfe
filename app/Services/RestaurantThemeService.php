<?php

namespace App\Services;

use App\Models\RestaurantThemeSetting;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use JsonException;

class RestaurantThemeService
{
    private const SETTING_ID = 1;

    public function __construct(
        private readonly ThemeRegistry $themes,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function defaultClientTheme(): array
    {
        $storedKey = RestaurantThemeSetting::query()
            ->whereKey(self::SETTING_ID)
            ->value('default_client_theme_key');

        if (! is_string($storedKey)) {
            return $this->themes->fallbackTheme();
        }

        return $this->themes->findPublishedTheme($storedKey)
            ?? $this->themes->fallbackTheme();
    }

    /**
     * @throws JsonException
     */
    public function defaultClientThemeKey(): string
    {
        return $this->defaultClientTheme()['key'];
    }

    /**
     * @throws JsonException
     * @throws ValidationException
     */
    public function setDefaultClientTheme(
        string $key,
        User $updatedBy,
    ): RestaurantThemeSetting {
        $theme = $this->themes->findPublishedTheme($key);

        if ($theme === null) {
            throw ValidationException::withMessages([
                'default_client_theme_key' => [
                    'The selected theme is unavailable.',
                ],
            ]);
        }

        return RestaurantThemeSetting::query()->updateOrCreate(
            ['id' => self::SETTING_ID],
            [
                'default_client_theme_key' => $key,
                'updated_by' => $updatedBy->getKey(),
            ],
        );
    }
}
