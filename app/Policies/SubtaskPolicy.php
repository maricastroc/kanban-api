<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Subtask;
use App\Models\User;

class SubtaskPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subtask $subtask): bool
    {
        return $subtask->task->column->board->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subtask $subtask): bool
    {
        return $subtask->task->column->board->user_id === $user->id;
    }
}
