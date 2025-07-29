<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreBoardRequest",
 *     type="object",
 *     title="Board Creation Request",
 *     description="Payload used to create a new board, optionally including initial columns.",
 *     required={"name"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the board to be created. Must be between 3 and 50 characters.",
 *         example="New Development Board"
 *     ),
 *     @OA\Property(
 *         property="columns",
 *         type="array",
 *         description="(Optional) List of columns to be created along with the board.",
 *
 *         @OA\Items(
 *             type="object",
 *             required={"name"},
 *
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="Name of the column. Must be between 3 and 50 characters.",
 *                 example="To Do"
 *             ),
 *             @OA\Property(
 *                 property="order",
 *                 type="integer",
 *                 description="(Optional) Display order of the column.",
 *                 example=1
 *             )
 *         )
 *     )
 * )
 */
class StoreBoardRequest {}
