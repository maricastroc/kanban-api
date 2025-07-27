<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\UniqueColumnNameInBoard;
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
        $boardId = $this->route('board')?->id;
        $columns = $this->input('columns', []);

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
            'columns.*.uuid' => 'sometimes',
            'columns.*.name' => [
                'required_with:columns',
                'string',
                'min:3',
                'max:255',
                function ($attribute, $value, $fail) use ($columns): void {
                    $index = explode('.', $attribute)[1];
                    $rule = new UniqueColumnNameInBoard($columns, $index);
                    if (! $rule->passes($attribute, $value)) {
                        $fail(str_replace(':value', $value, $rule->message()));
                    }
                },
            ],
            'columns.*.order' => 'sometimes|integer',
        ];
    }
}
