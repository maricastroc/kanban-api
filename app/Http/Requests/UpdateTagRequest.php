<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateTagRequest",
 *     type="object",
 *     title="Tag Update Request",
 *     description="Payload to update an existing tag.",
 *     required={"name", "color"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         minLength=3,
 *         maxLength=255,
 *         description="Name of the tag. Must be unique.",
 *         example="Urgent"
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         minLength=3,
 *         maxLength=255,
 *         description="Color associated with the tag. Must be unique.",
 *         example="#FF0000"
 *     )
 * )
 */
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
