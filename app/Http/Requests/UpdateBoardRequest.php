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
        return [
            'name' => 'sometimes|string|min:3|max:255|unique:boards,name,'.$this->route('board')->id,
            'is_active' => 'sometimes|boolean',
            'columns' => 'sometimes|array',
            'columns.*.id' => 'sometimes|exists:columns,id',
            'columns.*.name' => 'required_with:columns|string|min:3|max:255',
            'columns.*.order' => 'sometimes|integer',
        ];
    }
}
