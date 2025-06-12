<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use App\Models\Task;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SubtaskController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreSubtaskRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $task = Task::with('column.board')->findOrFail($request->task_id);

            $this->authorize('create', [Subtask::class, $task->column->board]);

            $order = Subtask::where('task_id', $task->id)->count();

            $subtask = Subtask::create([
                ...$request->validated(),
                'order' => $order,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subtask created successfully!',
                'data' => new SubtaskResource($subtask),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subtask',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateSubtaskRequest $request, Subtask $subtask): JsonResponse
    {
        try {
            $this->authorize('update', $subtask);

            $subtask->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subtask updated successfully!',
                'data' => new SubtaskResource($subtask),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subtask',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Subtask $subtask): JsonResponse
    {
        try {
            $this->authorize('delete', $subtask);

            $subtask->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subtask',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
