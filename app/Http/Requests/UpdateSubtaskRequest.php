<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSubtaskRequest",
 *     type="object",
 *     title="Subtask Update Request",
 *     description="Payload to update an existing subtask.",
 *     required={"name"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Name of the subtask. Must be between 3 and 255 characters.",
 *         example="Write unit tests"
 *     ),
 *     @OA\Property(
 *         property="is_completed",
 *         type="boolean",
 *         nullable=true,
 *         description="Completion status of the subtask.",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="order",
 *         type="integer",
 *         nullable=true,
 *         description="Ordering index of the subtask relative to siblings.",
 *         example=1
 *     )
 * )
 */
class UpdateSubtaskRequest extends FormRequest
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
                'max:255',
            ],
            'is_completed' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ];
    }
}
