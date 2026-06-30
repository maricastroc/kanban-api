<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        // Single place that turns exceptions into JSON for API requests. The
        // raw exception message is only exposed for 5xx when APP_DEBUG is on,
        // so internal details never leak in production.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            // Defer to Laravel's standard 422 { message, errors } response.
            if ($e instanceof ValidationException) {
                return null;
            }

            $status = match (true) {
                $e instanceof AuthenticationException => 401,
                $e instanceof AuthorizationException => 403,
                $e instanceof ModelNotFoundException => 404,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            $message = match (true) {
                $status === 404 => 'Resource not found.',
                $status >= 500 => 'An unexpected error occurred.',
                $e->getMessage() !== '' => $e->getMessage(),
                $status === 401 => 'Unauthenticated.',
                default => 'This action is unauthorized.',
            };

            $payload = ['success' => false, 'message' => $message];

            if ($status >= 500 && config('app.debug')) {
                $payload['error'] = $e->getMessage();
            }

            return response()->json($payload, $status);
        });
    })->create();
