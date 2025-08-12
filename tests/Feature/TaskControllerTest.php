<?php

use App\Models\Board;
use App\Models\Column;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->board = Board::factory()->create(['user_id' => $this->user->id]);
    $this->column = Column::factory()->create(['board_id' => $this->board->id]);
});

test('Unauthenticated user cannot access tasks', function (): void {
    \Illuminate\Support\Facades\Auth::logout();

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(401);
});

test('I should be able to store a task with subtasks and tags', function (): void {
    $payload = [
        'name' => 'New Task',
        'description' => 'Description here',
        'column_id' => $this->column->id,
        'due_date' => now()->addWeek()->toISOString(),
        'subtasks' => [
            ['name' => 'Subtask A'],
            ['name' => 'Subtask B'],
        ],
        'tags' => [],
    ];

    $response = $this->postJson('/api/tasks', $payload);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'New Task'])
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('tasks', ['name' => 'New Task']);
    $this->assertDatabaseHas('subtasks', ['name' => 'Subtask A']);
    $this->assertDatabaseHas('subtasks', ['name' => 'Subtask B']);
});

test('I should not be able to create a task in another user’s board', function (): void {
    $otherUser = User::factory()->create();
    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);
    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);

    $response = $this->postJson('/api/tasks', [
        'name' => 'Hacker Task',
        'column_id' => $foreignColumn->id,
    ]);

    $response->assertStatus(422);
});

test('I should be able to update a task with subtasks', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);
    $task->subtasks()->create(['name' => 'Old Subtask']);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'name' => 'Updated Task',
        'subtasks' => [
            ['name' => 'New Subtask'],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Task'])
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('subtasks', ['name' => 'Old Subtask']);
    $this->assertDatabaseHas('subtasks', ['name' => 'New Subtask']);
});

test('I should not be able to update another user’s task', function (): void {
    $otherUser = User::factory()->create();
    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);
    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);
    $foreignTask = Task::factory()->create(['column_id' => $foreignColumn->id]);

    $response = $this->putJson("/api/tasks/{$foreignTask->id}", [
        'name' => 'Hack Update',
    ]);

    $response->assertStatus(403);
});

test('I should be able to delete my own task', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Task deleted successfully!']);

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('I should not be able to delete another user’s task', function (): void {
    $otherUser = User::factory()->create();
    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);
    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);
    $foreignTask = Task::factory()->create(['column_id' => $foreignColumn->id]);

    $response = $this->deleteJson("/api/tasks/{$foreignTask->id}");

    $response->assertStatus(403);
});

test('I should be able to list my tasks with subtasks and tags', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $task->subtasks()->create(['name' => 'Subtask X']);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'subtasks',
                    'tags',
                ],
            ],
        ]);
});

test('I should not be able to create a task with subtasks that have no name', function (): void {
    $response = $this->postJson('/api/tasks', [
        'name' => 'Task with invalid subtasks',
        'column_id' => $this->column->id,
        'subtasks' => [
            ['name' => ''],
            ['is_completed' => true],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'subtasks.0.name',
            'subtasks.1.name',
        ]);
});

test('I should not be able to update a task with subtasks that have no name', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'subtasks' => [
            ['name' => 'Valid subtask'],
            ['name' => ''],
            ['is_completed' => false],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'subtasks.1.name',
            'subtasks.2.name',
        ]);
});

test('I should not update task with invalid subtask ID', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'name' => 'Task with invalid subtask ID',
        'subtasks' => [
            [
                'id' => 999999,
                'name' => 'Hacked Subtask',
            ],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'subtasks.0.id',
        ]);

    $this->assertDatabaseMissing('subtasks', ['name' => 'Hacked Subtask']);
});

test('I should not see tasks from other users', function (): void {
    $otherUser = User::factory()->create();

    $foreignBoard = Board::factory()->create(['user_id' => $otherUser->id]);

    $foreignColumn = Column::factory()->create(['board_id' => $foreignBoard->id]);

    Task::factory()->create(['column_id' => $foreignColumn->id]);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200);

    $this->assertCount(0, $response->json('data'));
});

test('I should be able to update task tags', function (): void {
    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'tags' => [$tag->id],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('task_tag', [
        'task_id' => $task->id,
        'tag_id' => $tag->id,
    ]);
});

test('I should not be able to use another user\'s tag', function (): void {
    $otherUser = User::factory()->create();

    $foreignTag = Tag::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->postJson('/api/tasks', [
        'name' => 'Tag Test',
        'column_id' => $this->column->id,
        'tags' => [$foreignTag->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tags.0']);
});

test('I should get validation errors when required fields are missing', function (): void {
    $response = $this->postJson('/api/tasks', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'column_id']);
});

test('I should be able to create a task with the get correct order based on column', function (): void {
    Task::factory()->create(['column_id' => $this->column->id]);
    Task::factory()->create(['column_id' => $this->column->id]);

    $payload = [
        'name' => 'Third Task',
        'column_id' => $this->column->id,
    ];

    $response = $this->postJson('/api/tasks', $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('tasks', [
        'name' => 'Third Task',
        'order' => 3,
    ]);
});

test('I should be able to move task to another column', function (): void {
    $newColumn = Column::factory()->create(['board_id' => $this->board->id]);

    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'column_id' => $newColumn->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'column_id' => $newColumn->id,
    ]);
});

test('Task can be created with empty subtasks array', function (): void {
    $task = Task::createWithSubtasks([
        'name' => 'Empty Subtasks',
        'column_id' => $this->column->id,
        'subtasks' => [],
    ]);

    $this->assertCount(0, $task->subtasks);
});

test('Subtasks should be deleted when task is deleted', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $subtask = $task->subtasks()->create(['name' => 'To be deleted']);

    $this->deleteJson("/api/tasks/{$task->id}");

    $this->assertDatabaseMissing('subtasks', ['id' => $subtask->id]);
});


test('Invalid due_date should be handled gracefully', function () {
    $response = $this->postJson('/api/tasks', [
        'name' => 'Invalid Date',
        'column_id' => $this->column->id,
        'due_date' => 'invalid-date',
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});