<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateTagRequest",
 *     type="object",
 *     title="Tag Update Request",
 *     description="Payload to update an existing tag.",
 *     required={"name", "color"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         minLength=3,
 *         maxLength=255,
 *         description="Name of the tag. Must be unique.",
 *         example="Urgent"
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         minLength=3,
 *         maxLength=255,
 *         description="Color associated with the tag. Must be unique.",
 *         example="#FF0000"
 *     )
 *
 *
 * )
 */
class UpdateTagRequest {}
