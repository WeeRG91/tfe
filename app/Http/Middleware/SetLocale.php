<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('locales.supported'));

        $locale = $request->user()?->locale
            ?? $request->session()->get('locale', config('app.locale'));

        $fallback = config('app.fallback_locale');

        if (! in_array($fallback, $supportedLocales, true)) {
            $fallback = config('app.locale', 'en');
        }

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = $fallback;
        }

        $request->session()->put('locale', $locale);

        app()->setLocale($locale);

        return $next($request);
    }
}
