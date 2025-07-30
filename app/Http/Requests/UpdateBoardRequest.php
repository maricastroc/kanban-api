<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->route('board')?->id;
        $this->input('columns', []);

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
            ],
            'is_active' => 'sometimes|boolean',
            'columns' => 'sometimes|array',
            'columns.*.id' => 'nullable|exists:columns,id',
            'columns.*.name' => [
                'required_with:columns',
                'string',
                'min:3',
                'max:50',
            ],
            'columns.*.order' => 'sometimes|integer',
        ];
    }
}
