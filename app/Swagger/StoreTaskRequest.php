<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreTaskRequest",
 *     type="object",
 *     title="Task Creation Request",
 *     description="Payload to create a new task with optional subtasks and tags.",
 *     required={"name", "column_id"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the task. Must be between 3 and 255 characters.",
 *         example="Implement authentication"
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
 *         property="column_id",
 *         type="integer",
 *         description="ID of the column where the task belongs. Must be valid and belong to the authenticated user.",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="due_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Optional due date for the task in YYYY-MM-DD format.",
 *         example="2025-08-15"
 *     ),
 *     @OA\Property(
 *         property="subtasks",
 *         type="array",
 *         description="Optional list of subtasks for this task.",
 *
 *         @OA\Items(
 *             type="object",
 *             required={"name"},
 *
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="Name of the subtask.",
 *                 example="Create login page"
 *             ),
 *             @OA\Property(
 *                 property="is_completed",
 *                 type="boolean",
 *                 description="Subtask completion status.",
 *                 example=false
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         description="Optional list of tag IDs to associate with this task.",
 *
 *         @OA\Items(type="integer", example=2)
 *     )
 * )
 */
class StoreTaskRequest {}
