<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserSyncRequest;
use App\Models\User;

class UserSyncController extends Controller
{
    public function __invoke(UserSyncRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }
}
