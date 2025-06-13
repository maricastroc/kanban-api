<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    public function login(LoginRequest $r): JsonResponse
    {
        try {
            $user = User::firstWhere('email', $r->string('email'));

            if (! $user || ! Hash::check($r->string('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user->tokens()->delete();

            return response()->json([
                'token' => $user->createToken('auth_token')->plainTextToken,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred. Please try again later.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout successful!',
            ]);
        }

        return response()->json([
            'message' => 'User not authenticated',
        ], 401);
    }
}
