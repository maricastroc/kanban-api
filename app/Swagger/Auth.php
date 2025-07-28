<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for registration, login, and authentication management via Sanctum"
 * )
 */
class Auth
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
    public function register(): void
    {
        //
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
    public function login(): void
    {
        //
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
    public function logout(): void
    {
        //
    }
}
