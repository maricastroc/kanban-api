<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithCookie
{
    /**
     * If the request carries the auth token in an httpOnly cookie (and no
     * Authorization header is present), promote it to a Bearer header so
     * Sanctum's guard can authenticate the request transparently.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->bearerToken() && $request->cookie('auth_token')) {
            $request->headers->set('Authorization', 'Bearer '.$request->cookie('auth_token'));
        }

        return $next($request);
    }
}
