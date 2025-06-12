<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $tags = Tag::all();

            return response()->json([
                'data' => [
                    'tags' => $tags,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
