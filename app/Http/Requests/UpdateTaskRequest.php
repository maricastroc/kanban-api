<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\UniqueSubtaskNameInTask;
use App\Rules\UniqueTaskNameInColumn;
use Illuminate\Foundation\Http\FormRequest;

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
            'column_id' => 'required|exists:columns,id',
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueTaskNameInColumn($task),
            ],
            'description' => 'nullable|string|max:255',
            'uuid' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'subtasks' => 'sometimes|array',
            'subtasks.*.uuid' => 'sometimes',
            'subtasks.*.name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($taskId): void {
                    $index = explode('.', $attribute)[1];
                    $subtasks = $this->input('subtasks');
                    $subtask = $subtasks[$index] ?? [];

                    $rule = new UniqueSubtaskNameInTask($taskId, $subtask);
                    if (! $rule->passes($attribute, $value)) {
                        $fail($rule->message());
                    }
                },
            ],
            'subtasks.*.is_completed' => 'sometimes|boolean',
            'tags' => 'sometimes|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
