<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\UniqueColumnNameInBoard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateBoardRequest",
 *     type="object",
 *     title="Board Update Request",
 *     description="Payload to update an existing board, optionally including columns.",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="New name of the board (optional). Must be between 3 and 50 characters and unique per user.",
 *         example="Updated Development Board"
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="Flag to set the board as active or inactive.",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="columns",
 *         type="array",
 *         description="List of columns to update or add in the board.",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="uuid",
 *                 type="string",
 *                 description="UUID of the column to update. Omit for new columns.",
 *                 example="a3f1e4c2-7b3d-4f57-9e23-12d4b1a3c456"
 *             ),
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="Name of the column. Required if columns property is present.",
 *                 example="In Progress"
 *             ),
 *             @OA\Property(
 *                 property="order",
 *                 type="integer",
 *                 description="Order of the column in the board. Lower values appear first.",
 *                 example=2
 *             )
 *         )
 *     )
 * )
 */
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
                'max:50',
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
                'max:50',
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
