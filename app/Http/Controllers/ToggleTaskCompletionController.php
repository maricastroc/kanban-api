<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ToggleTaskCompletionController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        DB::transaction(function () use ($task): void {
            $task->is_completed = ! $task->is_completed;
            $task->save();

            // Completing a task ticks off its whole checklist so the card's
            // progress matches the done state. Re-opening it leaves the
            // subtasks untouched.
            if ($task->is_completed) {
                $task->subtasks()->update(['is_completed' => true]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Task completion toggled successfully!',
            'data' => new TaskResource($task->fresh(['subtasks', 'column'])),
        ]);
    }
}
