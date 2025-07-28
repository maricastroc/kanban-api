<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
 *         nullable=true,
 *         description="Optional short description of the task.",
 *         example="Add login and registration functionality."
 *     ),
 *     @OA\Property(
 *         property="column_id",
 *         type="integer",
 *         description="ID of the column where this task belongs. Must belong to the authenticated user's board.",
 *         example=5
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
class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => 'nullable|string|max:255',
            'column_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail): void {
                    if (! Column::where('id', $value)
                        ->whereHas('board', fn ($q) => $q->where('user_id', Auth::id()))
                        ->exists()) {
                        $fail('Invalid column selected');
                    }
                },
            ],
            'due_date' => 'nullable|date',
            'subtasks' => 'sometimes|array',
            'subtasks.*.name' => [
                'required',
                'string',
                'min:3',
                'max:255',
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
