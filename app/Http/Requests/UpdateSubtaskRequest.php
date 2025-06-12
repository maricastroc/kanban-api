<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubtaskRequest extends FormRequest
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
                'max:255',
                Rule::unique('subtasks')
                    ->where(fn ($query) => $query->where('task_id', $this->subtask->task_id))
                    ->ignore($this->subtask->id),
            ],
            'is_completed' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ];
    }
}
