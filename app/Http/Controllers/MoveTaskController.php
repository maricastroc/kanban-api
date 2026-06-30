<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoveTaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'new_column_id' => ['required', 'exists:columns,id'],
            'new_order' => ['required', 'integer', 'min:0'],
        ]);

        $newColumnId = $validated['new_column_id'];
        $newOrder = (int) $validated['new_order'];

        $targetColumn = Column::findOrFail($newColumnId);

        // a task can only move within its own board (the task is already
        // authorized as the user's, so this also blocks cross-tenant moves)
        if ($targetColumn->board_id !== $task->column->board_id) {
            abort(403, 'Cannot move a task to a column outside its board.');
        }

        $currentColumnId = $task->column_id;
        $currentOrder = $task->order;

        if ($newColumnId === $currentColumnId && $newOrder === $currentOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Task already in the correct column and position.',
                'data' => $task,
            ]);
        }

        DB::transaction(function () use ($task, $currentColumnId, $currentOrder, $newColumnId, $newOrder, $targetColumn): void {
            Task::where('column_id', $currentColumnId)
                ->where('order', '>', $currentOrder)
                ->decrement('order');

            Task::where('column_id', $newColumnId)
                ->where('order', '>=', $newOrder)
                ->increment('order');

            $task->update([
                'column_id' => $newColumnId,
                'order' => $newOrder,
                'status' => $targetColumn->name,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Task moved successfully!',
            'data' => $task,
        ]);
    }
}
