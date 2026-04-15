<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(fn (Request $request) => route(
            $request->user()?->redirectRouteName() ?? 'home'
        ));
        $middleware->validateCsrfTokens([
            'stripe/webhook',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'auth' => \App\Http\Middleware\CheckAuth::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (TokenMismatchException $exception): void {
            $request = request();

            logger()->warning('CSRF token mismatch', [
                'method' => $request->method(),
                'full_url' => $request->fullUrl(),
                'host' => $request->getHost(),
                'scheme' => $request->getScheme(),
                'referer' => $request->headers->get('referer'),
                'origin' => $request->headers->get('origin'),
                'session_driver' => config('session.driver'),
                'session_cookie' => config('session.cookie'),
                'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
                'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                'has_form_token' => $request->request->has('_token'),
                'app_url' => config('app.url'),
            ]);
        });
    })->create();
