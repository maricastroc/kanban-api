<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\UniqueColumnNameInBoard;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $columns = $this->input('columns', []);

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('boards')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'columns' => 'sometimes|array',
            'columns.*.name' => [
                'required',
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
