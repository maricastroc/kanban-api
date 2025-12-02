<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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
                ->when(
                    $request->string('with')->contains('columns'),
                    fn ($query) => $query->with('columns.tasks.subtasks')
                )
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

    public function show(Board $board): JsonResponse
    {
        try {
            $this->authorize('view', $board);

            $board->activate();

            $board->load([
                'columns.tasks.subtasks',
                'user',
            ]);

            return response()->json([
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
                'error' => $e->getMessage(),
            ], 403);
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

            $board = Board::createWithColumns($request->only(['name', 'columns']), $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Board created successfully!',
                'data' => [
                    'board' => new BoardResource($board->load('columns.tasks.subtasks')),
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
            $this->authorize('update', $board);

            $board = $board->updateWithColumns($request->only(['name', 'is_active', 'columns']));

            $board->load('columns.tasks.subtasks');

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
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
                'error' => $e->getMessage(),
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function active(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $board = Board::getActiveBoard($user->id);

            if (!$board) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'board' => null,
                    ],
                ], 200);
            }

            if ($board->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            $this->authorize('view', $board);

            return response()->json([
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching for active board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setActive(Board $board): JsonResponse
    {
        try {
            $this->authorize('update', $board);

            $board->activate();

            $board->load([
                'columns.tasks.subtasks',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Board set as active.',
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set active board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
