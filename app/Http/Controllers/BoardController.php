<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
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
    }

    public function show(Board $board): JsonResponse
    {
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
    }

    public function store(StoreBoardRequest $request): JsonResponse
    {
        $user = Auth::user();

        $board = Board::createWithColumns($request->only(['name', 'columns']), $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Board created successfully!',
            'data' => [
                'board' => new BoardResource($board->load('columns.tasks.subtasks')),
            ],
        ], 201);
    }

    public function update(UpdateBoardRequest $request, Board $board): JsonResponse
    {
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
    }

    public function destroy(Board $board): JsonResponse
    {
        $this->authorize('delete', $board);

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Board deleted successfully!',
        ]);
    }

    public function active(): JsonResponse
    {
        $user = Auth::user();

        // getActiveBoard() is already scoped to the user, so the returned board
        // (if any) is guaranteed to be theirs — no extra authorization needed.
        $board = Board::getActiveBoard($user->id);

        if (! $board) {
            return response()->json([
                'success' => true,
                'data' => [
                    'board' => null,
                ],
            ], 200);
        }

        return response()->json([
            'data' => [
                'board' => new BoardResource($board),
            ],
        ], 200);
    }

    public function setActive(Board $board): JsonResponse
    {
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
    }
}
