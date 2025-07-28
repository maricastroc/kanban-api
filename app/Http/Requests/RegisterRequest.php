<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     title="Register Request",
 *     description="Payload used to register a new user.",
 *     required={"name", "email", "password"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Full name of the user.",
 *         example="Jane Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Valid email address. Must be unique.",
 *         example="jane.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Password for the new account.",
 *         example="MySecurePassword123"
 *     )
 * )
 */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string'],
        ];
    }
}
