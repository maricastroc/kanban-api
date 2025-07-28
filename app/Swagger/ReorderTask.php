<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations related to task reordering within a column"
 * )
 */
class ReorderTask
{
    /**
     * @OA\Patch(
     *     path="/api/tasks/{task}/reorder",
     *     summary="Reorder a task within the same column",
     *     description="Changes the position of a task within its column, automatically updating other task positions accordingly.",
     *     operationId="reorderTask",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to reorder",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="New position index of the task",
     *
     *         @OA\JsonContent(
     *             required={"new_order"},
     *
     *             @OA\Property(
     *                 property="new_order",
     *                 type="integer",
     *                 example=2,
     *                 description="New position (zero-based index)"
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
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
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
     *         description=""Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
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
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while reordering task",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
}
