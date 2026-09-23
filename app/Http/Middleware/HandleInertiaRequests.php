<?php

namespace App\Http\Middleware;

use App\Http\Resources\GlobalUserResource;
use App\Services\ThemeRegistry;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        $themeContext = $request->attributes->get('themeContext', []);

        if (! is_array($themeContext)) {
            $themeContext = [];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => app()->getLocale(),
            'fallbackLocale' => config('app.fallback_locale'),
            'availableLocales' => config('locales.supported'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'theme' => [
                'surface' => $themeContext['surface'] ?? 'client',
                'selection' => $themeContext['selection']
                    ?? 'restaurant-default',
                'restaurantDefaultKey' => $themeContext['restaurantDefaultKey']
                    ?? 'builtin/light',
                'customThemes' => app(ThemeRegistry::class)->allPublishedCustomThemes(),
            ],
            'features' => [
                'companyDelivery' => (bool) config(
                    'restaurant.delivery.company.enabled',
                ),
            ],
            'auth' => [
                'user' => $user
                    ? new GlobalUserResource($user)
                    : null,
                'roles' => $user
                    ? $user->getRoleNames()
                    : [],
                'permissions' => $user
                    ? $user->getAllPermissions()->pluck('name')
                    : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'createdIngredient' => fn () => $request->session()->get('createdIngredient'),
                'createdMeat' => fn () => $request->session()->get('createdMeat'),
            ],
        ];
    }
}
