<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('locales.supported'));

        $fallbackLocale = config('app.fallback_locale', 'en');

        if (! in_array($fallbackLocale, $supportedLocales, true)) {
            $fallbackLocale = config('app.locale', 'en');
        }

        $locale = $request->getPreferredLanguage($supportedLocales) ?? $fallbackLocale;

        app()->setLocale($locale);

        return $next($request);
    }
}
