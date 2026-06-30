<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    public function register(RegisterRequest $register): JsonResponse
    {
        $user = User::create([
            'name' => $register->name,
            'email' => $register->email,
            'password' => $register->password,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201)->cookie($this->authCookie($token));
    }

    public function login(LoginRequest $r): JsonResponse
    {
        $user = User::firstWhere('email', $r->string('email'));

        if (! $user || ! Hash::check($r->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200)->cookie($this->authCookie($token));
    }

    public function logout(): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user) {
            /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();

            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'message' => 'Logout successful!',
        ])->cookie($this->forgetAuthCookie());
    }

    /**
     * Builds the httpOnly cookie that carries the Sanctum token. Kept out of
     * JavaScript's reach (mitigates token theft via XSS) and promoted back to a
     * Bearer header by App\Http\Middleware\AuthenticateWithCookie.
     */
    private function authCookie(string $token): Cookie
    {
        return cookie(
            name: 'auth_token',
            value: $token,
            minutes: 60 * 24 * 7, // 1 week
            path: config('session.path', '/'),
            domain: config('session.domain'),
            secure: (bool) (config('session.secure') ?? app()->isProduction()),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site', 'lax'),
        );
    }

    private function forgetAuthCookie(): Cookie
    {
        return cookie()->forget(
            'auth_token',
            config('session.path', '/'),
            config('session.domain'),
        );
    }
}
