<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSubtaskRequest",
 *     type="object",
 *     title="Subtask Creation Request",
 *     description="Payload used to create a new subtask associated with an existing task.",
 *     required={"name", "task_id"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the subtask. Must be between 3 and 255 characters.",
 *         example="Write unit tests"
 *     ),
 *     @OA\Property(
 *         property="task_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the task that this subtask belongs to. Must exist in the database.",
 *         example=42
 *     )
 * )
 */
class StoreSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'task_id' => 'required|exists:tasks,id',
        ];
    }
}
