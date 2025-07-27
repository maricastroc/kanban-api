<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for registration, login, and authentication management via Sanctum"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *
     *             @OA\Property(property="name", type="string", example="Jon Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jon@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", minLength=8)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully!",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="User registered successfully!"),
     *             @OA\Property(property="token", type="string", example="1|Xn9r5..."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Jon Doe"),
     *                 @OA\Property(property="email", type="string", example="jon@example.com")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="object", example={"email": {"The email field is required."}})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticates a user (returns Sanctum token)",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="jon@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login!",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="1|Xn9r5...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="The provided credentials are incorrect.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="object", example={"email": {"The provided credentials are incorrect."}})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Revokes the current access token",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful!",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Logout successful!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
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
