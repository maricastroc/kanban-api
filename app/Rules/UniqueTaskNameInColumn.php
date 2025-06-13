<?php

namespace App\Rules;

use App\Models\Task;
use Illuminate\Contracts\Validation\Rule;

class UniqueTaskNameInColumn implements Rule
{
    public function __construct(protected \App\Models\Task $task) {}

    public function passes($attribute, $value): bool
    {
        return ! Task::where('column_id', $this->task->column_id)
            ->where('name', $value)
            ->where('id', '!=', $this->task->id)
            ->exists();
    }

    public function message(): string
    {
        return "A task named ':input' already exists in this column.";
    }
}
