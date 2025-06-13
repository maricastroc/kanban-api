<?php

namespace App\Rules;

use App\Models\Subtask;
use Illuminate\Contracts\Validation\Rule;

class UniqueSubtaskNameInTask implements Rule
{
    public function __construct(protected int $taskId, protected array $subtask) {}

    public function passes($attribute, $value): bool
    {
        if (! isset($this->subtask['uuid'])) {
            return true;
        }

        return ! Subtask::where('name', $value)
            ->where('task_id', $this->taskId)
            ->where('uuid', '!=', $this->subtask['uuid'])
            ->exists();
    }

    public function message(): string
    {
        return "A subtask named ':input' already exists in this task.";
    }
}
