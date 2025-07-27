<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Subtasks",
 *     description="Subtask-related operations"
 * )
 */
class SubtaskController extends Controller
{
    use AuthorizesRequests;

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
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Subtask"
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
     *         response=500,
     *         description="Internal error while switching status",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function toggleCompletion(Subtask $subtask): JsonResponse
    {
        try {
            $this->authorize('update', $subtask);

            $subtask->is_completed = ! $subtask->is_completed;
            $subtask->save();

            return response()->json([
                'success' => true,
                'message' => 'Subtask completion toggled successfully!',
                'data' => new SubtaskResource($subtask),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle subtask completion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
