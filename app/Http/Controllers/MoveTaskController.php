<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Task;
use Exception;
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

        $currentColumnId = $task->column_id;
        $currentOrder = $task->order;

        if ($newColumnId === $currentColumnId && $newOrder === $currentOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Task already in the correct column and position.',
                'data' => $task,
            ]);
        }

        try {
            DB::beginTransaction();

            // ❗️Validação para impedir duplicação de nomes na nova coluna
            $existingTask = Task::where('column_id', $newColumnId)
                ->where('name', $task->name)
                ->where('id', '!=', $task->id)
                ->first();

            if ($existingTask) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'A task with the same name already exists in the target column.',
                ], 422);
            }

            Task::where('column_id', $currentColumnId)
                ->where('order', '>', $currentOrder)
                ->decrement('order');

            Task::where('column_id', $newColumnId)
                ->where('order', '>=', $newOrder)
                ->increment('order');

            $task->update([
                'column_id' => $newColumnId,
                'order' => $newOrder,
                'status' => Column::findOrFail($newColumnId)->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task moved successfully!',
                'data' => $task,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to move task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
