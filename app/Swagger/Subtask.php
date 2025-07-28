<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Subtasks",
 *     description="Subtask-related operations"
 * )
 */
class Subtask
{
    /**
     * @OA\Patch(
     *     path="/api/subtasks/{subtask}/toggle-completion",
     *     summary="Toggles the completion status of a subtask",
     *     description="Inverts the current completion status (completed/incomplete) of a subtask",
     *     operationId="toggleSubtaskCompletion",
     *     tags={"Subtasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="subtask",
     *         in="path",
     *         description="Subtask ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status changed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subtask completion toggled successfully!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subtask")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
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
     *         description="Subtask not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Subtask] 5")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal error while switching status",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function toggleCompletion(): void {}
}
