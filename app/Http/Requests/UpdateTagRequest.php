<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
                Rule::unique('tags', 'name')->ignore($this->tag->id),
            ],
            'color' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('tags', 'color')->ignore($this->tag->id),
            ],
        ];
    }
}
