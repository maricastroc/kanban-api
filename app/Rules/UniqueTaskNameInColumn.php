<?php

namespace App\Rules;

use App\Models\Task;
use Illuminate\Contracts\Validation\Rule;

class UniqueTaskNameInColumn implements Rule
{
    public function __construct(
        protected int $columnId,
        protected ?int $ignoreTaskId = null
    ) {}

    public function passes($attribute, $value): bool
    {
        $query = Task::where('column_id', $this->columnId)
            ->where('name', $value);

        if ($this->ignoreTaskId !== null && $this->ignoreTaskId !== 0) {
            $query->where('id', '!=', $this->ignoreTaskId);
        }

        return ! $query->exists();
    }

    public function message(): string
    {
        return "A task with the name ':input' already exists in this column.";
    }
}
