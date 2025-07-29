<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     type="object",
 *     title="Task Update Request",
 *     description="Payload for updating an existing task with optional fields and nested subtasks and tags.",
 *
 *     @OA\Property(
 *         property="column_id",
 *         type="integer",
 *         description="ID of the column where the task belongs. Must be valid and belong to the authenticated user.",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Name of the task. Must be between 3 and 255 characters.",
 *         example="Implement login feature"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=500,
 *         nullable=true,
 *         description="Optional description for the task. Must have a maximum of 500 characters.",
 *         example="Detailed description about the login feature implementation."
 *     ),
 *     @OA\Property(
 *         property="due_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Due date for the task in ISO 8601 format.",
 *         example="2025-08-15"
 *     ),
 *     @OA\Property(
 *         property="subtasks",
 *         type="array",
 *         description="Optional list of subtasks belonging to the task.",
 *
 *         @OA\Items(
 *             type="object",
 *             required={"name"},
 *
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 maxLength=255,
 *                 description="Name of the subtask. Must be between 3 and 255 characters.",
 *                 example="Create UI for login"
 *             ),
 *             @OA\Property(
 *                 property="is_completed",
 *                 type="boolean",
 *                 nullable=true,
 *                 description="Subtask completion status.",
 *                 example=false
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         description="Optional list of tag IDs to associate with the task.",
 *
 *         @OA\Items(
 *             type="integer",
 *             example=5
 *         )
 *     )
 * )
 */
class UpdateTaskRequest {}
