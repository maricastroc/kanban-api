<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task-related operations"
 * )
 */
class ReorderTaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Patch(
     *     path="/api/tasks/{task}/reorder",
     *     summary="Reorder a task in the same column",
     *     description="Changes the position of a task within the same column, automatically reordering the other tasks",
     *     operationId="reorderTask",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to be reordered",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="New task order",
     *
     *         @OA\JsonContent(
     *             required={"new_order"},
     *
     *             @OA\Property(
     *                 property="new_order",
     *                 type="integer",
     *                 example=2,
     *                 description="New column position (based on index 0)"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Task successfully reordered",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task reordered successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Task"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Task is already in the requested position",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task already in correct position.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="new_order",
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The new order must be at least 0.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *  *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Task] 123"),
     *             @OA\Property(property="exception", type="string", example="Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal error while reordering task",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

        try {
            DB::beginTransaction();

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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task reordered successfully!',
                'data' => $task,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
