<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
            'due_date' => 'nullable|date',
            'subtasks' => 'sometimes|array',
            'subtasks.*.name' => 'required|string|min:3|max:255',
            'subtasks.*.is_completed' => 'sometimes|boolean',
        ];
    }
}
