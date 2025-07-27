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

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations related to task movement"
 * )
 */
class MoveTaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Patch(
     *     path="/api/tasks/{task}/move",
     *     summary="Move a task to another column/position",
     *     description="Move a task to a new column and position, automatically reordering the remaining tasks",
     *     operationId="moveTask",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to be moved",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data for task movement",
     *
     *         @OA\JsonContent(
     *             required={"new_column_id", "new_order"},
     *
     *             @OA\Property(
     *                 property="new_column_id",
     *                 type="integer",
     *                 example=2,
     *                 description="Target column ID"
     *             ),
     *             @OA\Property(
     *                 property="new_order",
     *                 type="integer",
     *                 example=0,
     *                 description="New position in column (0-based index)"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Task moved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task moved successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Task already in requested position",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task already in the correct column and position."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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
