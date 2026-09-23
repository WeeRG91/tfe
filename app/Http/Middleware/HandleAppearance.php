<?php

namespace App\Http\Middleware;

use App\Services\RestaurantThemeService;
use App\Services\ThemeRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

readonly class HandleAppearance
{
    public function __construct(
        private ThemeRegistry $themes,
        private RestaurantThemeService $restaurantThemes,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     *
     * @throws JsonException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $surface = $this->surface($request);
        $selection = $this->selection($request, $surface);

        $restaurantDefault = $this->restaurantThemes->defaultClientTheme();

        $initialTheme = $this->resolveInitialTheme(
            $selection,
            $surface,
            $restaurantDefault,
        );

        $context = [
            'surface' => $surface,
            'selection' => $selection,
            'restaurantDefaultKey' => $restaurantDefault['key'],
            'initialTheme' => $initialTheme,
            'systemLightTheme' => $this->themes->fallbackTheme(),
            'systemDarkTheme' => $this->themes
                ->findBuiltInTheme('builtin/dark'),
        ];

        View::share('themeContext', $context);

        $request->attributes->set('themeContext', $context);

        return $next($request);
    }

    private function surface(Request $request): string
    {
        return $request->is(
            'admin',
            'admin/*',
            'settings',
            'settings/*',
        ) ? 'admin' : 'client';
    }

    /**
     * @throws JsonException
     */
    private function selection(Request $request, string $surface): string
    {
        $cookieName = $surface === 'admin'
            ? 'admin_theme'
            : 'client_theme';

        $selection = $request->cookie($cookieName);

        if ($selection === null && $surface === 'admin') {
            $selection = $request->cookie('appearance');
        }

        $selection = match ($selection) {
            'light' => 'builtin/light',
            'dark' => 'builtin/dark',
            default => $selection,
        };

        $fallback = $surface === 'admin'
            ? 'system'
            : 'restaurant-default';

        if (! is_string($selection)) {
            return $fallback;
        }

        if ($selection === 'system') {
            return $selection;
        }

        if (
            $selection === 'restaurant-default' &&
            $surface === 'client'
        ) {
            return $selection;
        }

        if ($this->themes->findPublishedTheme($selection) !== null) {
            return $selection;
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>  $restaurantDefault
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function resolveInitialTheme(
        string $selection,
        string $surface,
        array $restaurantDefault,
    ): array {
        if ($selection === 'restaurant-default' && $surface === 'client') {
            return $restaurantDefault;
        }

        if ($selection === 'system') {
            return $this->themes->fallbackTheme();
        }

        return $this->themes->findPublishedTheme($selection)
            ?? ($surface === 'client'
                ? $restaurantDefault
                : $this->themes->fallbackTheme());
    }
}
