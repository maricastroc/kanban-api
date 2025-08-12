<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id(); // Obtém o ID do usuário autenticado

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('tags', 'name')
                    ->where('user_id', $userId),
            ],
            'color' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('tags', 'color')
                    ->where('user_id', $userId),
            ],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a tag with this name.',
            'color.unique' => 'You already have a tag with this color.',
        ];
    }
}
