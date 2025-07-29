<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Column;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'description' => 'nullable|string|max:500',
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
