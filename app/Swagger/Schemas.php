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
 * @OA\Info(
 *     title="Kanban API",
 *     version="1.0.0",
 *     description="Backend API for Kanban App — RESTful service managing users, boards, tasks, and authentication for daily task management."
 * )
 *
 * @OA\Schema(
 *     schema="Board",
 *     type="object",
 *     title="Board",
 *     description="Represents a board, including its columns and archived status.",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier of the board.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the board. Must be between 3 and 50 characters.",
 *         example="My Project Board"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Indicates whether this board is currently selected and being worked on by the user.",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="columns",
 *         type="array",
 *         description="List of columns that belong to this board.",
 *
 *         @OA\Items(ref="#/components/schemas/Column")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Column",
 *     required={"name", "order"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier of the column.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the column. Must be between 3 and 50 characters.",
 *         example="To-do"
 *     ),
 *     @OA\Property(
 *         property="order",
 *         type="integer",
 *         description="(Optional) Display order of the column.",
 *         example="1",
 *     ),
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
 *     required={"name", "column_id"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier of the task.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the task. Must be between 3 and 255 characters.",
 *         example="To-do"
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
 *      @OA\Property(
 *         property="order",
 *         type="integer",
 *         nullable=true,
 *         description="Position order of the task in the column. Minimum value is 0.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="due_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Optional due date for the task in YYYY-MM-DD format.",
 *         example="2025-08-15"
 *     ),
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
 *     required={"name", "task_id", "is_completed"},
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier of the subtask.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the subtask. Must be between 3 and 255 characters.",
 *         example="Subtask"
 *     ),
 *     @OA\Property(
 *         property="task_id",
 *         type="integer",
 *         description="ID of the task where the subtask belongs. Must be valid and belong to the authenticated user.",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="is_completed",
 *         type="boolean",
 *         description="Indicates whether the subtask is completed (true) or not (false).",
 *         example=false
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 *    @OA\Schema(
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
 *  @OA\Schema(
 *     schema="SuccessResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         description="Response data"
 *     )
 * )
 *
 *  @OA\Schema(
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
 *
 *  @OA\Schema(
 *     schema="NotFoundError",
 *
 *     @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Tag] 5"),
 *     @OA\Property(property="exception", type="string", example="Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException"),
 * )
 *
 * @OA\Schema(
 *     schema="ConflictError",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Tag is already linked to the task"
 *     ),
 *     @OA\Property(
 *         property="error_type",
 *         type="string",
 *         example="Conflict"
 *     )
 * )
 */
class Schemas {}
