<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyNextAuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key(env('NEXTAUTH_SECRET'), 'HS256'));

            $user = User::where('email', $decoded->email)->first();

            if (! $user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            Auth::login($user);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token: '.$e->getMessage()], 401);
        }

        return $next($request);
    }
}
