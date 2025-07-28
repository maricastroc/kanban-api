<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Subtasks",
 *     description="Operations related to subtasks"
 * )
 */
class BulkReorderSubtasks
{
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
     *                 description="Array of subtasks with their new positions",
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
     *                 )
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
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "subtasks": {"The subtasks field is required."}
     *                 }
     *             )
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
     *                 example="No query results for model [App\Models\Subtask] 123"
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
    public function __invoke(): void
    {
        //
    }
}
