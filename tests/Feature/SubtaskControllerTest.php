<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->board = Board::factory()->create(['user_id' => $this->user->id]);
    $this->column = Column::factory()->create(['board_id' => $this->board->id]);
});

test('I should be able to toggle subtask completion', function (): void {
    $this->actingAs($this->user);

    $task = Task::factory()->create(['column_id' => $this->column->id]);
    $subtask = Subtask::factory()->create([
        'task_id' => $task->id,
        'is_completed' => false,
    ]);

    $response = $this->patchJson("/api/subtasks/{$subtask->id}/toggle-completion");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertTrue($subtask->fresh()->is_completed);
});

test('I should not be able to toggle another user\'s subtask completion', function (): void {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();

    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);
    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);

    $foreignTask = Task::factory()->create(['column_id' => $foreignColumn->id]);

    $foreignSubtask = Subtask::factory()->create(['task_id' => $foreignTask->id]);

    $response = $this->patchJson("/api/subtasks/{$foreignSubtask->id}/toggle-completion");

    $response->assertStatus(403);
});

test('I should be able to reorder subtasks in bulk', function (): void {
    $this->actingAs($this->user);

    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $subtasks = Subtask::factory()->count(3)->create([
        'task_id' => $task->id,
        'order' => [1, 2, 3],
    ]);

    $newOrder = [
        ['id' => $subtasks[0]->id, 'order' => 3],
        ['id' => $subtasks[1]->id, 'order' => 1],
        ['id' => $subtasks[2]->id, 'order' => 2],
    ];

    $response = $this->patchJson('/api/subtasks/reorder', [
        'taskId' => $task->id,
        'subtasks' => $newOrder,
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertEquals(3, $subtasks[0]->fresh()->order);
    $this->assertEquals(1, $subtasks[1]->fresh()->order);
    $this->assertEquals(2, $subtasks[2]->fresh()->order);
});

test('Subtask order is automatically set on creation', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);
    Subtask::factory()->create(['task_id' => $task->id, 'order' => 1]);

    $newSubtask = Subtask::factory()->create(['task_id' => $task->id]);

    $this->assertEquals(2, $newSubtask->order);
});

test('Subtask belongs to task relationship', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);
    $subtask = Subtask::factory()->create(['task_id' => $task->id]);

    $this->assertEquals($task->id, $subtask->task->id);
    $this->assertTrue($task->subtasks->contains($subtask));
});
