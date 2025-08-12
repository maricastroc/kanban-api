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

    $response = $this->getJson('/api/tags');

    $response->assertStatus(401);
});

test('I should be able to list all tags', function (): void {
    $this->actingAs($this->user);

    Tag::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/tags');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'tags' => [
                    '*' => ['id', 'name', 'color'],
                ],
            ],
        ]);
});

test('I should be able to create a tag', function (): void {
    $response = $this->postJson('/api/tags', [
        'name' => 'Urgent',
        'color' => '#ff0000',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['name' => 'Urgent']);
});

test('I should not be able to create a tag with an existing name or color', function (): void {
    $this->postJson('/api/tags', ['name' => 'Urgent', 'color' => '#ff0000']);

    $response = $this->postJson('/api/tags', [
        'name' => 'Urgent',
        'color' => '#00ff00',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $response = $this->postJson('/api/tags', [
        'name' => 'Urgent 2',
        'color' => '#ff0000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('I should be able to update a tag', function (): void {
    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'Updated Name',
        'color' => '#0000ff',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Name']);
});

test('I should not be able to update tag with existing name or color', function (): void {
    Tag::factory()->create(['user_id' => $this->user->id, 'name' => 'Existing', 'color' => '#ff0000']);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'Existing',
        'color' => '#0000ff',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'Updated',
        'color' => '#ff0000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('I should be able to delete a tag', function (): void {
    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/tags/{$tag->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('User cannot see other users tags', function (): void {
    $otherUser = User::factory()->create();

    Tag::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->getJson('/api/tags');

    $response->assertStatus(200);

    $this->assertCount(0, $response->json('data.tags'));
});

test('I should not be able to see other users tags', function (): void {
    $otherUser = User::factory()->create();

    Tag::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->getJson('/api/tags');

    $response->assertStatus(200);

    $this->assertCount(0, $response->json('data.tags'));
});

test('I should not be able to update other users tags', function (): void {
    $otherUser = User::factory()->create();

    $tag = Tag::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'Hacked Tag',
        'color' => '#000000',
    ]);

    $response->assertStatus(403);
});

test('I should not be able to delete other users tags', function (): void {
    $otherUser = User::factory()->create();

    $tag = Tag::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->deleteJson("/api/tags/{$tag->id}");

    $response->assertStatus(403);
});

test('I can only create a tag when providing a name and a color', function (): void {
    $response = $this->postJson('/api/tags', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'color']);
});

test('I can only create a tag when providing a name with at least 3 characters', function (): void {
    $response = $this->postJson('/api/tags', [
        'name' => 'ab',
        'color' => '#ff0000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('Tag deletion removes task associations', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $task->tags()->attach($tag->id);

    $this->deleteJson("/api/tags/{$tag->id}");

    $this->assertDatabaseMissing('task_tag', ['tag_id' => $tag->id]);
});

test('I should be able to attach tag to task', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/tasks/{$task->id}/tags/{$tag->id}");

    $response->assertStatus(200);

    $this->assertDatabaseHas('task_tag', [
        'task_id' => $task->id,
        'tag_id' => $tag->id,
    ]);
});

test('I should not be able to attach same tag twice', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $this->postJson("/api/tasks/{$task->id}/tags/{$tag->id}");

    $response = $this->postJson("/api/tasks/{$task->id}/tags/{$tag->id}");

    $response->assertStatus(409);
});

test('I should not be able to attach tag to another user\'s task', function (): void {
    $otherUser = User::factory()->create();

    $foreignTask = Task::factory()->create([
        'column_id' => Column::factory()->create([
            'board_id' => Board::factory()->create(['user_id' => $otherUser->id]),
        ]),
    ]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/tasks/{$foreignTask->id}/tags/{$tag->id}");

    $response->assertStatus(403);
});

test('I should be able to detach tag from task', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $task->tags()->attach($tag->id);

    $response = $this->deleteJson("/api/tasks/{$task->id}/tags/{$tag->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('task_tag', [
        'task_id' => $task->id,
        'tag_id' => $tag->id,
    ]);
});

test('I should not be able to detach non-attached tag', function (): void {
    $task = Task::factory()->create(['column_id' => $this->column->id]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/tasks/{$task->id}/tags/{$tag->id}");

    $response->assertStatus(409);
});

test('I should not be able to detach tag to another user\'s task', function (): void {
    $otherUser = User::factory()->create();

    $foreignTask = Task::factory()->create([
        'column_id' => Column::factory()->create([
            'board_id' => Board::factory()->create(['user_id' => $otherUser->id]),
        ]),
    ]);

    $tag = Tag::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/tasks/{$foreignTask->id}/tags/{$tag->id}");

    $response->assertStatus(403);
});
