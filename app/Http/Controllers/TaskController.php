<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
            }

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

    public function show(int $id): JsonResponse
    {
        try {
            $task = Task::with(['subtasks', 'tags'])->findOrFail($id);

            return response()->json(['data' => new TaskResource($task)], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
            }

            $task = Task::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'data' => [
                    'task' => new TaskResource($task),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);

            $task->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'data' => [
                    'task' => new TaskResource($task),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);

            $task->delete();

            return response()->json(['message' => 'Task deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
