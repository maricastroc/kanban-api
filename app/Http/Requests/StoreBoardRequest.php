<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->input('columns', []);

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
            ],
            'columns' => 'sometimes|array',
            'columns.*.name' => [
                'required',
                'string',
                'min:3',
                'max:50',
            ],
            'columns.*.order' => 'sometimes|integer',
        ];
    }
}
