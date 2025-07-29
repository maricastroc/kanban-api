<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreTagRequest",
 *     type="object",
 *     title="Tag Creation Request",
 *     description="Payload used to create a new tag.",
 *     required={"name"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the tag. Must be between 3 and 255 characters.",
 *         example="Urgent"
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         nullable=true,
 *         description="Optional color for the tag, as a string (e.g., hex code or color name). Must be between 3 and 255 characters.",
 *         example="#FF0000"
 *     )
 * )
 */
class StoreTagRequest {}
