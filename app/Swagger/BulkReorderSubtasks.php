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
     *     summary="Reorder multiple subtasks",
     *     description="Reorders multiple subtasks within a specific task in one request.",
     *     operationId="bulkReorderSubtasks",
     *     tags={"Subtasks"},
     *     security={{"sanctum":{}}},

     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payload containing the task ID and a list of subtasks with their new order.",
     *
     *         @OA\JsonContent(
     *             required={"taskId", "subtasks"},
     *
     *             @OA\Property(
     *                 property="taskId",
     *                 type="integer",
     *                 example=5,
     *                 description="ID of the parent task."
     *             ),
     *             @OA\Property(
     *                 property="subtasks",
     *                 type="array",
     *                 minItems=1,
     *                 description="List of subtasks and their new positions.",
     *
     *                 @OA\Items(
     *                     required={"id", "order"},
     *
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=12,
     *                         description="ID of the subtask to reorder."
     *                     ),
     *                     @OA\Property(
     *                         property="order",
     *                         type="integer",
     *                         minimum=0,
     *                         example=0,
     *                         description="New position (0-based index)."
     *                     )
     *                 )
     *             )
     *         )
     *     ),

     *
     *     @OA\Response(
     *         response=200,
     *         description="Subtasks reordered successfully.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subtasks reordered successfully.")
     *         )
     *     ),

     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request structure or data.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Invalid subtasks data."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "taskId": {"The taskId field is required."},
     *                     "subtasks": {"The subtasks field must be a non-empty array."}
     *                 }
     *             )
     *         )
     *     ),

     *
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),

     *
     *     @OA\Response(
     *         response=403,
     *         description="User is not authorized to reorder subtasks in this task.",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),

     *
     *     @OA\Response(
     *         response=404,
     *         description="Specified task or subtask not found.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No query results for model [App\Models\Subtask] 123."
     *             )
     *         )
     *     ),

     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed for one or more fields.",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),

     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
}
