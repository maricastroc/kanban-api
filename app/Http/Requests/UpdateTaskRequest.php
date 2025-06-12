<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks')
                    ->where(fn ($query) => $query->where('column_id', $this->input('column_id')))
                    ->ignore($this->task->id),
            ],
            'description' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'due_date' => 'nullable|date',
        ];
    }
}
