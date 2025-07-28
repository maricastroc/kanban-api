<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations related to task management"
 * )
 */
class MoveTask
{
    /**
     * @OA\Patch(
     *     path="/api/tasks/{task}/move",
     *     summary="Move a task to another column/position",
     *     description="Repositions a task to a new column and/or order, automatically reordering the other tasks in both columns.",
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
     *         description="Task movement data",
     *
     *         @OA\JsonContent(
     *             required={"new_column_id", "new_order"},
     *
     *             @OA\Property(
     *                 property="new_column_id",
     *                 type="integer",
     *                 example=2,
     *                 description="ID of the target column"
     *             ),
     *             @OA\Property(
     *                 property="new_order",
     *                 type="integer",
     *                 example=0,
     *                 description="New position index within the target column (0-based)"
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
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Task already in the requested position",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task already in the correct column and position."),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Task or column not found",
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
}
