<?php

namespace App\Services;

use App\Models\CustomTheme;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class ThemeRegistry
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $themesByKey = null;

    public function __construct(
        private readonly ?string $builtInThemesPath = null,
    ) {}

    /**
     * @return list<array<string, mixed>>
     *
     * @throws JsonException
     */
    public function allBuiltInThemes(): array
    {
        return array_values($this->loadBuiltInThemes());
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws JsonException
     */
    public function findBuiltInTheme(string $key): ?array
    {
        return $this->loadBuiltInThemes()[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function fallbackTheme(): array
    {
        return $this->findBuiltInTheme('builtin/light')
            ?? throw new RuntimeException(
                'The required built-in light theme is missing.',
            );
    }

    public function managedCustomThemeDefinition(CustomTheme $theme): array
    {
        return [
            'id' => $theme->id,
            ...$this->customDefinition($theme),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allCustomThemes(): array
    {
        return CustomTheme::query()
            ->orderBy('name')
            ->get()
            ->map(fn (CustomTheme $theme): array => $this->managedCustomThemeDefinition($theme))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allPublishedCustomThemes(): array
    {
        return CustomTheme::query()
            ->where('published', true)
            ->orderBy('name')
            ->get()
            ->map(fn (CustomTheme $theme): array => $this->customDefinition($theme))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     * @throws JsonException
     */
    public function allPublishedThemes(): array
    {
        $builtIns = array_values(array_filter(
            $this->allBuiltInThemes(),
            static fn (array $theme): bool => ($theme['published'] ?? false) === true,
        ));

        $custom = $this->allPublishedCustomThemes();

        return [...$builtIns, ...$custom];
    }

    /**
     * @return array<string, mixed>|null
     * @throws JsonException
     */
    public function findPublishedTheme(string $key): ?array
    {
        $builtIn = $this->findBuiltInTheme($key);

        if ($builtIn !== null) {
            return ($builtIn['published'] ?? false) === true
                ? $builtIn
                : null;
        }

        if (!str_starts_with($key, 'custom/')) {
            return null;
        }

        $custom = CustomTheme::query()
            ->where('key', $key)
            ->where('published', true)
            ->first();

        return $custom === null
            ? null
            : $this->customDefinition($custom);
    }

    private function customDefinition(CustomTheme $theme): array
    {
        return [
            'key' => $theme->key,
            'name' => $theme->name,
            'source' => 'custom',
            'mode' => $theme->mode,
            'colors' => $theme->colors,
            'radius' => $theme->radius,
            'published' => $theme->published,
            'editable' => true,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @throws JsonException
     */
    private function loadBuiltInThemes(): array
    {
        if ($this->themesByKey !== null) {
            return $this->themesByKey;
        }

        $path = $this->builtInThemesPath
            ?? resource_path('themes/built-in-themes.json');

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(
                "Unable to read the built-in themes file at [$path].",
            );
        }

        $themes = json_decode(
            $contents,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (! is_array($themes) || ! array_is_list($themes)) {
            throw new UnexpectedValueException(
                'The built-in themes file must contain a JSON array.',
            );
        }

        $themesByKey = [];

        foreach ($themes as $index => $theme) {
            if (! is_array($theme)) {
                throw new UnexpectedValueException(
                    "Theme at index $index must be an object.",
                );
            }

            $key = $theme['key'] ?? null;

            if (! is_string($key) || $key === '') {
                throw new UnexpectedValueException(
                    "Theme at index $index has an invalid key.",
                );
            }

            if (isset($themesByKey[$key])) {
                throw new UnexpectedValueException(
                    "Duplicate built-in theme key [$key].",
                );
            }

            $themesByKey[$key] = $theme;
        }

        return $this->themesByKey = $themesByKey;
    }
}
