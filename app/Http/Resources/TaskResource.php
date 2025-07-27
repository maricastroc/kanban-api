<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'column_id' => $this->column_id,
            'column_name' => $this->column->name ?? null,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'order' => $this->order,
            'due_date' => $this->due_date,
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
