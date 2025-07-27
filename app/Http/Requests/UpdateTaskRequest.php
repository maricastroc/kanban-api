<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\UniqueSubtaskNameInTask;
use App\Rules\UniqueTaskNameInColumn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $currentColumnId = $task?->column_id;

        return [
            'column_id' => [
                'required',
                'integer',
                Rule::exists('columns', 'id')->where(function ($query): void {
                    $query->whereHas('board', function ($q): void {
                        $q->where('user_id', auth()->id());
                    });
                }),
                function ($attribute, $value, $fail) use ($currentColumnId): void {
                    if ($value == $currentColumnId) {
                        $fail('Task is already in this column.');
                    }
                },
            ],
            'name' => [
                'required',
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
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
