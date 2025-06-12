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
        ];
    }
}
