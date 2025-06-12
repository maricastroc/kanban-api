<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreColumnRequest;
use App\Http\Requests\UpdateColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Board;
use App\Models\Column;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColumnController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('viewAny', Column::class);

            $columns = Column::whereHas('board', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })->get();

            return response()->json([
                'data' => ColumnResource::collection($columns),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreColumnRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $board = Board::findOrFail($request->board_id);

            $this->authorize('update', $board);

            $column = Column::create([
                'name' => $request->name,
                'board_id' => $board->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Column created successfully!',
                'data' => new ColumnResource($column),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create column.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Column $column): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('view', $column);

            $column = Column::where('id', $id)
                ->whereHas('board', fn ($q) => $q->where('user_id', $user->id))
                ->first();

            if (! $column) {
                return response()->json(['success' => false, 'message' => 'Column not found.'], 404);
            }

            return response()->json(['data' => new ColumnResource($column)], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateColumnRequest $request, Column $column): JsonResponse
    {
        try {
            $this->authorize('update', $column);

            $column->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Column updated successfully!',
                'data' => new ColumnResource($column),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update column.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Column $column): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('delete', $column);

            $column->delete();

            return response()->json(['message' => 'Column deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete column.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
