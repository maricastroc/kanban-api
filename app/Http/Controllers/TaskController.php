<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Column;
use App\Models\Task;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('viewAny', Task::class);

            $tasks = Task::with(['subtasks', 'tags'])
                ->whereHas('column.board', function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                })
                ->get();

            return response()->json(['data' => TaskResource::collection($tasks)], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $column = Column::with('board')->findOrFail($request->column_id);

            $this->authorize('create', [$column->board]);

            $task = Task::createWithSubtasks($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'data' => [
                    'task' => new TaskResource($task->load('subtasks')),
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $this->authorize('update', $task);

            $task = $task->updateWithSubtasks($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'data' => [
                    'task' => new TaskResource($task->load('subtasks')),
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('delete', $task);

            $task->delete();

            return response()->json(['message' => 'Task deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
