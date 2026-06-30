<?php

use App\Models\Board;
use App\Models\Column;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->board = Board::factory()->create(['user_id' => $this->user->id]);
    $this->column = Column::factory()->create(['board_id' => $this->board->id]);
    $this->task = Task::factory()->create(['column_id' => $this->column->id]);
});

test('toggling completion marks the task complete and ticks all its subtasks', function (): void {
    Subtask::factory()->count(3)->create(['task_id' => $this->task->id]);

    $response = $this->patchJson("/api/tasks/{$this->task->id}/toggle-completion");

    $response->assertStatus(200)->assertJsonPath('success', true);

    expect($this->task->fresh()->is_completed)->toBeTrue();
    expect(
        Subtask::where('task_id', $this->task->id)->where('is_completed', false)->count()
    )->toBe(0);
});

test('toggling again re-opens the task and leaves subtasks untouched', function (): void {
    Subtask::factory()->count(2)->create(['task_id' => $this->task->id]);
    // The Subtask "creating" hook forces is_completed=false, so flip via a mass
    // update to set up the "all done" starting state.
    Subtask::where('task_id', $this->task->id)->update(['is_completed' => true]);
    $this->task->update(['is_completed' => true]);

    $this->patchJson("/api/tasks/{$this->task->id}/toggle-completion")
        ->assertStatus(200);

    expect($this->task->fresh()->is_completed)->toBeFalse();
    expect(
        Subtask::where('task_id', $this->task->id)->where('is_completed', true)->count()
    )->toBe(2);
});

test('a task with no subtasks can still be completed', function (): void {
    $this->patchJson("/api/tasks/{$this->task->id}/toggle-completion")
        ->assertStatus(200);

    expect($this->task->fresh()->is_completed)->toBeTrue();
});

test('a user cannot toggle the completion of another user\'s task', function (): void {
    $otherUser = User::factory()->create();
    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);
    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);
    $foreignTask = Task::factory()->create(['column_id' => $foreignColumn->id]);

    $this->patchJson("/api/tasks/{$foreignTask->id}/toggle-completion")
        ->assertStatus(403);
});
