<?php

namespace App\Swagger;

/**
 * @OA\Schema(
 *     schema="Board",
 *     required={"name", "user_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Meu Quadro"),
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
 *     required={"title", "description"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Tarefa 1"),
 *     @OA\Property(property="description", type="string", example="Descrição da tarefa"),
 *     @OA\Property(
 *         property="subtasks",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Subtask")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Subtask",
 *     required={"title", "is_completed"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Subtask 1"),
 *     @OA\Property(property="is_completed", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error", type="string", example="Error details")
 * )
 */
class Schemas {}
