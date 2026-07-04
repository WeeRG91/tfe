<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Inertia\Inertia;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payments/stripe/webhook',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($request->expectsJson()) {
                return $response;
            }

            if ($response->getStatusCode() === 419) {
                return back()->with('message', 'The page expired, please refresh the page and try again.');
            }

            if (
                $exception instanceof InvalidSignatureException &&
                $request->routeIs('activate.show', 'activate.store')
            ) {
                return redirect()->route('activate.expired');
            }

            if (!in_array($response->getStatusCode(), [403, 404, 429, 500, 503], true)) {
                return $response;
            }

            $adminSections = [
                'dashboard',
                'dishes',
                'drinks',
                'ingredients',
                'allergens',
                'meats',
                'images',
                'global-search',
            ];

            $currentSection = (string) $request->segment(1);
            $isAdminSection = in_array($currentSection, $adminSections, true);
            $isLikelyAdminSection = collect($adminSections)
                ->contains(fn (string $section) => levenshtein($currentSection, $section) <= 2);
            $errorPage = $isAdminSection || $isLikelyAdminSection
                ? 'errors/AdminError'
                : 'errors/ClientError';

            return Inertia::render($errorPage, [
                'status' => $response->getStatusCode(),
            ])->toResponse($request)->setStatusCode($response->getStatusCode());
        });
    })->create();
