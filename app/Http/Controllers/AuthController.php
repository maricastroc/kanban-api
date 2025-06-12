<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $register)
    {
        try {
            $user = User::create([
                'name' => $register->name,
                'email' => $register->email,
                'password' => $register->password,
            ]);

            return response()->json([
                'message' => 'User registered successfully!',
                'token' => $user->createToken('auth_token')->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $r): array
    {
        try {
            $user = User::firstWhere('email', $r->string('email'));

            if (! $user || ! Hash::check($r->string('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user->tokens()->delete();

            return [
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];

        } catch (\Exception $e) {
            return [
                'error' => 'An unexpected error occurred. Please try again later.',
                'details' => $e->getMessage(),
            ];
        }
    }
}
