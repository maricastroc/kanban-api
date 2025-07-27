<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use App\Models\Tag;
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
            'column_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail): void {
                    if (! Column::where('id', $value)
                        ->whereHas('board', fn ($q) => $q->where('user_id', auth()->id()))
                        ->exists()) {
                        $fail('Invalid column selected');
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
            'tags.*' => [
                'integer',
                'exists:tags,id',
                function ($attribute, $value, $fail): void {
                    if (! Tag::where('id', $value)
                        ->where('user_id', auth()->id())
                        ->exists()) {
                        $fail('Invalid tag selected');
                    }
                },
            ],
        ];
    }
}
