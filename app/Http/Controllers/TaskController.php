<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $this->authorize('viewAny', Task::class);

        $tasks = Task::with(['subtasks', 'tags'])
            ->whereHas('column.board', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->get();

        return response()->json(['data' => TaskResource::collection($tasks)], 200);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $user = Auth::user();

        $column = Column::where('id', $request->column_id)
            ->whereHas('board', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (! $column) {
            abort(403, 'Unauthorized to create task in this column.');
        }

        $task = Task::createWithSubtasks($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully!',
            'data' => [
                'task' => new TaskResource($task->load('subtasks')),
            ],
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $task->updateWithSubtasks($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully!',
            'data' => [
                'task' => new TaskResource($task->load('subtasks')),
            ],
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully!'], 200);
    }
}
