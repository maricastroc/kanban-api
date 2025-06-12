<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('viewAny', Board::class);

            $boards = Board::where('user_id', $user->id)
                ->with([
                    'columns.tasks.subtasks',
                ])
                ->when(
                    $request->string('with')->contains('user'),
                    fn ($query) => $query->with('user')
                )
                ->get();

            return response()->json([
                'data' => [
                    'boards' => BoardResource::collection($boards),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreBoardRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $board = Board::create([
                'name' => $request->input('name'),
                'user_id' => $user->id,
                'is_active' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Board created successfully!',
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateBoardRequest $request, Board $board): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('update', $board);

            $board->update($request->only(['name', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'Board updated successfully!',
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Board $board): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('delete', $board);

            if (! $user || $board->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            $board->delete();

            return response()->json([
                'success' => true,
                'message' => 'Board deleted successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
