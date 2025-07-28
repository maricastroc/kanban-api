<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Subtasks",
 *     description="Operations related to subtasks reorder"
 * )
 */
class BulkReorderSubtaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Patch(
     *     path="/api/subtasks/reorder",
     *     summary="Bulk reorder subtasks",
     *     description="Update the order of multiple subtasks belonging to a specific task in a single operation",
     *     operationId="bulkReorderSubtasks",
     *     tags={"Subtasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Subtasks reorder data",
     *
     *         @OA\JsonContent(
     *             required={"taskId", "subtasks"},
     *
     *             @OA\Property(
     *                 property="taskId",
     *                 type="integer",
     *                 example=5,
     *                 description="ID of the parent task"
     *             ),
     *             @OA\Property(
     *                 property="subtasks",
     *                 type="array",
     *                 minItems=1,
     *
     *                 @OA\Items(
     *                     required={"id", "order"},
     *
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=12,
     *                         description="ID of the subtask to reorder"
     *                     ),
     *                     @OA\Property(
     *                         property="order",
     *                         type="integer",
     *                         example=1,
     *                         minimum=0,
     *                         description="New position of the subtask (0-based index)"
     *                     )
     *                 ),
     *                 description="Array of subtasks with their new positions"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Subtasks reordered successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subtasks reordered successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Invalid subtasks data"),
     *             @OA\Property(property="errors", type="object", example={
     *                 "subtasks": {"The subtasks field is required."}
     *             })
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Task or subtask not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No query results for model [App\\Models\\Subtask] 123"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
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
}
