<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateBoardRequest",
 *     type="object",
 *     title="Board Update Request",
 *     description="Payload to update an existing board, optionally including columns.",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="New name of the board (optional). Must be between 3 and 50 characters.",
 *         example="Updated Development Board"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Flag to set the board as active or inactive.",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="columns",
 *         type="array",
 *         description="List of columns to update or add in the board.",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="uuid",
 *                 type="string",
 *                 description="UUID of the column to update. Omit for new columns.",
 *                 example="a3f1e4c2-7b3d-4f57-9e23-12d4b1a3c456"
 *             ),
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="Name of the column. Required if columns property is present.",
 *                 example="In Progress"
 *             ),
 *             @OA\Property(
 *                 property="order",
 *                 type="integer",
 *                 description="Order of the column in the board. Lower values appear first.",
 *                 example=2
 *             )
 *         )
 *     )
 * )
 */
class UpdateBoardRequest {}
