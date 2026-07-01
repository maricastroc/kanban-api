<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Columns",
 *     description="Operations related to column reordering within a board"
 * )
 */
class ReorderColumn
{
    /**
     * @OA\Patch(
     *     path="/api/columns/{column}/reorder",
     *     summary="Reorder a column within its board",
     *     description="Changes the position of a column within its board, automatically updating the other columns' positions accordingly.",
     *     operationId="reorderColumn",
     *     tags={"Columns"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="column",
     *         in="path",
     *         required=true,
     *         description="ID of the column to reorder",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="New position of the column",
     *
     *         @OA\JsonContent(
     *             required={"new_order"},
     *
     *             @OA\Property(
     *                 property="new_order",
     *                 type="integer",
     *                 example=2,
     *                 minimum=1,
     *                 description="New position (1-based index)"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Column successfully reordered (or already in the requested position)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Column reordered successfully!")
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
     *         description="Column not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Column] 5")
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
     *         description="Internal server error while reordering column",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function __invoke(): void {}
}
