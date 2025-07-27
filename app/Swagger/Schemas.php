<?php

namespace App\Swagger;

/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="X-XSRF-TOKEN"
 * )
 *
 * @OA\Schema(
 *     schema="Board",
 *     required={"name", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="My Board"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="columns",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Column")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Column",
 *     required={"title", "order"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="To Do"),
 *     @OA\Property(property="order", type="integer", example=1),
 *     @OA\Property(
 *         property="tasks",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Task")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Task",
 *     required={"title", "column_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Task title"),
 *     @OA\Property(property="description", type="string", example="Task description"),
 *     @OA\Property(property="column_id", type="integer", example=1),
 *     @OA\Property(property="order", type="integer", example=0),
 *     @OA\Property(property="status", type="string", example="To Do"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="subtasks",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Subtask")
 *     ),
 *
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Tag")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Subtask",
 *     required={"title", "task_id", "is_completed"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Subtask example"),
 *     @OA\Property(property="task_id", type="integer", example=1),
 *     @OA\Property(property="is_completed", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 *  * @OA\Schema(
 *     schema="Tag",
 *     required={"name", "color", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Urgent"),
 *     @OA\Property(property="color", type="string", example="#FF0000"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error", type="string", example="Error details")
 * )
 *
 *  * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="The given data was invalid."
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="new_column_id",
 *             type="array",
 *
 *             @OA\Items(type="string", example="The selected new_column_id is invalid.")
 *         ),
 *
 *         @OA\Property(
 *             property="new_order",
 *             type="array",
 *
 *             @OA\Items(type="string", example="The new_order must be at least 0.")
 *         )
 *     )
 * )
 */
class Schemas {}
