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
     *     summary="Reorder multiple subtasks of a task",
     *     operationId="reorderSubtasks",
     *     tags={"Subtasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Subtasks reorder data",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 required={"taskId", "subtasks"},
     *
     *                 @OA\Property(
     *                     property="taskId",
     *                     type="integer",
     *                     example=5,
     *                     description="ID of the parent task"
     *                 ),
     *                 @OA\Property(
     *                     property="subtasks",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         required={"id", "order"},
     *
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="order", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subtasks reordered successfully")
     *         )
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
