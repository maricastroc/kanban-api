<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->route('task');

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
            ],
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'subtasks' => 'sometimes|array',
            'subtasks.*.name' => [
                'required',
                'string',
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
