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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function bulkReorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'taskId' => ['required', 'integer', 'exists:tasks,id'],
            'subtasks' => ['required', 'array'],
            'subtasks.*.id' => ['required', 'integer', 'exists:subtasks,id'],
            'subtasks.*.order' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['subtasks'] as $subtaskData) {
                $subtask = Subtask::where('task_id', $validated['taskId'])
                    ->findOrFail($subtaskData['id']);

                $this->authorize('update', $subtask);

                $subtask->order = $subtaskData['order'];
                $subtask->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtasks reordered successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder subtasks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleCompletion(Subtask $subtask): JsonResponse
    {
        try {
            $this->authorize('update', $subtask);

            $subtask->is_completed = ! $subtask->is_completed;
            $subtask->save();

            return response()->json([
                'success' => true,
                'message' => 'Subtask completion toggled successfully!',
                'data' => new SubtaskResource($subtask),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle subtask completion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
