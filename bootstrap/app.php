<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // The auth token cookie is read raw by AuthenticateWithCookie, so keep
        // it out of Laravel's cookie encryption.
        $middleware->encryptCookies(except: [
            'auth_token',
        ]);

        // Promote the auth_token httpOnly cookie to a Bearer header so Sanctum
        // can authenticate cookie-based requests transparently.
        $middleware->api(prepend: [
            \App\Http\Middleware\AuthenticateWithCookie::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
