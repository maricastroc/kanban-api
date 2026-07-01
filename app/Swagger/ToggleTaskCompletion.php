<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations related to task completion"
 * )
 */
class ToggleTaskCompletion
{
    /**
     * @OA\Patch(
     *     path="/api/tasks/{task}/toggle-completion",
     *     summary="Toggles the completion status of a task",
     *     description="Inverts the current completion status of a task. Completing a task also marks all of its subtasks as completed; re-opening it leaves the subtasks untouched.",
     *     operationId="toggleTaskCompletion",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Task ID",
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
     *             @OA\Property(property="message", type="string", example="Task completion toggled successfully!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
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
     *         description="Task not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Task] 5")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal error while toggling completion",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function __invoke(): void {}
}
