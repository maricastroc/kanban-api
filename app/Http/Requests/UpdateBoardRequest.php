<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $boardId = $this->route('board')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                Rule::unique('boards')->ignore($boardId)->where(function ($query): void {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'is_active' => 'sometimes|boolean',
            'columns' => 'sometimes|array',
            'columns.*.id' => 'sometimes|integer|exists:columns,id',
            'columns.*.name' => 'required_with:columns|string|min:3|max:255',
            'columns.*.order' => 'sometimes|integer|min:0',
        ];
    }
}
