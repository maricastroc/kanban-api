<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReorderTaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'new_order' => ['required', 'integer', 'min:0'],
        ]);

        $newOrder = (int) $validated['new_order'];
        $currentOrder = $task->order;
        $columnId = $task->column_id;

        if ($newOrder === $currentOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Task already in correct position.',
            ]);
        }

        DB::transaction(function () use ($task, $columnId, $currentOrder, $newOrder): void {
            if ($newOrder < $currentOrder) {
                Task::where('column_id', $columnId)
                    ->whereBetween('order', [$newOrder, $currentOrder - 1])
                    ->increment('order');
            } else {
                Task::where('column_id', $columnId)
                    ->whereBetween('order', [$currentOrder + 1, $newOrder])
                    ->decrement('order');
            }

            $task->update(['order' => $newOrder]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Task reordered successfully!',
            'data' => $task,
        ]);
    }
}
