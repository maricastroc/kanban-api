<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use App\Models\Tag;
use App\Rules\UniqueSubtaskNameInTask;
use App\Rules\UniqueTaskNameInColumn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
 *         description="Name of the task, unique per column.",
 *         example="Implement login feature"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=500,
 *         nullable=true,
 *         description="Optional description for the task.",
 *         example="Detailed description about the login feature implementation."
 *     ),
 *     @OA\Property(
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
 *                 description="Name of the subtask, unique within the task.",
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
class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $task = $this->route('task');
        $taskId = $task?->id;

        return [
            'column_id' => [
                'sometimes',
                'integer',
                function ($attribute, $value, $fail): void {
                    if (! Column::where('id', $value)
                        ->whereHas('board', fn ($q) => $q->where('user_id', Auth::id()))
                        ->exists()) {
                        $fail('Invalid column selected');
                    }
                },
            ],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                new UniqueTaskNameInColumn($task),
            ],
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'subtasks' => 'sometimes|array',
            'subtasks.*.name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($taskId): void {
                    $index = explode('.', $attribute)[1];
                    $subtasks = $this->input('subtasks', []);
                    $subtask = $subtasks[$index] ?? [];

                    $rule = new UniqueSubtaskNameInTask($taskId, $subtask);
                    if (! $rule->passes($attribute, $value)) {
                        $fail($rule->message());
                    }
                },
            ],
            'subtasks.*.is_completed' => 'sometimes|boolean',
            'tags' => 'sometimes|array',
            'tags.*' => [
                'integer',
                'exists:tags,id',
                function ($attribute, $value, $fail): void {
                    if (! Tag::where('id', $value)
                        ->where('user_id', Auth::id())
                        ->exists()) {
                        $fail('Invalid tag selected');
                    }
                },
            ],
        ];
    }
}
