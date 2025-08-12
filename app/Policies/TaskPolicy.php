<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Column;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->column->board->user_id === $user->id;
    }

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
    public function update(User $user, Task $task): bool
    {
        return $task->column->board->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->column->board->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Column $column): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Column $column): bool
    {
        return false;
    }

    public function attachTag(User $user, Task $task, Tag $tag)
    {
        if ($task->column->board->user_id !== $user->id) {
            return false;
        }

        return $tag->user_id === $user->id;
    }

    public function detachTag(User $user, Task $task, Tag $tag)
    {
        if ($task->column->board->user_id !== $user->id) {
            return false;
        }

        return $tag->user_id === $user->id;
    }
}
