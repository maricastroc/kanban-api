<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('I should be able to list all boards', function (): void {
    $this->actingAs($this->user);

    Board::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/boards');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'boards' => [
                    '*' => ['id', 'name', 'is_active'],
                ],
            ],
        ]);
});

test('I should be able to show a board', function (): void {
    $board = Board::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user);

    $response = $this->getJson(route('api.boards.show', $board));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $board->id,
            'name' => $board->name,
        ]);
});

test('I should be able to store a board with columns', function (): void {
    $this->actingAs($this->user);

    $payload = [
        'name' => 'New Board',
        'columns' => [
            ['name' => 'To Do'],
            ['name' => 'Doing'],
            ['name' => 'Done'],
        ],
    ];

    $response = $this->postJson('/api/boards', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['name' => 'New Board']);

    $this->assertDatabaseHas('boards', ['name' => 'New Board', 'user_id' => $this->user->id]);
    $this->assertDatabaseHas('columns', ['name' => 'To Do']);
});

test('I should be able to update a board with columns', function (): void {
    $user = User::factory()->create();

    $board = Board::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $payload = [
        'name' => 'Updated Board Name',
        'columns' => [
            ['name' => 'Column 1'],
            ['name' => 'Column 2'],
        ],
    ];

    $response = $this->putJson(route('api.boards.update', $board), $payload);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['name' => 'Updated Board Name']);

    $this->assertDatabaseHas('boards', [
        'id' => $board->id,
        'name' => 'Updated Board Name',
    ]);

    $this->assertDatabaseHas('columns', [
        'name' => 'Column 1',
        'board_id' => $board->id,
    ]);
});

test('Unauthenticated user cannot access boards', function (): void {
    $response = $this->getJson('/api/boards');
    $response->assertStatus(401);
});

test('I shouldn\'t be able to access another user\'s board', function (): void {
    $otherUser = User::factory()->create();
    $board = Board::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user);

    $response = $this->getJson(route('api.boards.show', $board));
    $response->assertStatus(403);
});

test('I should be able to delete a board', function (): void {
    $board = Board::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);

    $response = $this->deleteJson(route('api.boards.destroy', $board));
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('I shouldn\'t be able to delete another user\'s board', function (): void {
    $otherUser = User::factory()->create();
    $board = Board::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user);

    $response = $this->deleteJson(route('api.boards.destroy', $board));
    $response->assertStatus(403);
});

test('Deleting board activates previous board', function (): void {
    $board1 = Board::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDay()]);
    $board2 = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

    $this->actingAs($this->user);
    $this->deleteJson(route('api.boards.destroy', $board2));

    $this->assertTrue($board1->fresh()->is_active);
});

test('I should be able to get active board', function (): void {
    $board = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);
    $this->actingAs($this->user);

    $response = $this->getJson('/api/boards/active');
    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $board->id]);
});

test('I should be able to set a boards as active', function (): void {
    $board1 = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);
    $board2 = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);

    $board2->activate();

    $board1->refresh();
    $board2->refresh();

    expect($board2->is_active)->toBeTrue();

    expect($board1->is_active)->toBeFalse();
});

test('Active board returns 404 when none active', function (): void {
    $this->actingAs($this->user);

    $response = $this->getJson('/api/boards/active');
    $response->assertStatus(404);
});

test('Board list can load columns and user', function (): void {
    Board::factory()->hasColumns(2)->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);

    $response = $this->getJson('/api/boards?with=columns,user');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'boards' => [
                    '*' => ['id', 'name', 'columns', 'user'],
                ],
            ],
        ]);
});

test('createWithColumns creates board with columns', function (): void {
    $board = Board::createWithColumns([
        'name' => 'Test',
        'columns' => [['name' => 'Col 1'], ['name' => 'Col 2']],
    ], $this->user->id);

    $this->assertDatabaseHas('boards', ['name' => 'Test']);
    $this->assertCount(2, $board->columns);
});

test('updateWithColumns syncs columns correctly', function (): void {
    $board = Board::factory()->hasColumns(2)->create(['user_id' => $this->user->id]);

    $board->updateWithColumns([
        'name' => 'Updated',
        'columns' => [
            ['id' => $board->columns[0]->id, 'name' => 'Updated Col'],
            ['name' => 'New Col'],
        ],
    ]);

    $this->assertDatabaseHas('columns', ['name' => 'Updated Col']);
    $this->assertDatabaseHas('columns', ['name' => 'New Col']);
    $this->assertCount(2, $board->fresh()->columns);
});

test('getActiveBoard returns correct board', function (): void {
    $inactive = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);
    $active = Board::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

    $result = Board::getActiveBoard($this->user->id);
    $this->assertEquals($active->id, $result->id);
});

test('deactivateOtherBoards deactivates all other boards for the user', function (): void {
    $user = User::factory()->create();

    $board1 = Board::factory()->create(['user_id' => $user->id, 'is_active' => true]);
    $board2 = Board::factory()->create(['user_id' => $user->id, 'is_active' => true]);
    $board3 = Board::factory()->create(['user_id' => $user->id, 'is_active' => true]);

    $board1->activate();

    $board1->refresh();
    $board2->refresh();
    $board3->refresh();

    $this->assertTrue($board1->is_active);
    $this->assertFalse($board2->is_active);
    $this->assertFalse($board3->is_active);
});

test('deactivateOtherBoards does not affect other users boards', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $boardUser1 = Board::factory()->create(['user_id' => $user1->id, 'is_active' => true]);
    $boardUser2 = Board::factory()->create(['user_id' => $user2->id, 'is_active' => true]);

    $boardUser1->activate();

    $boardUser2->refresh();

    $this->assertTrue($boardUser2->is_active);
});

test('board creation validation', function (): void {
    $this->actingAs($this->user);

    // Teste para nome vazio
    $response = $this->postJson('/api/boards', [
        'name' => '',
        'columns' => [['name' => 'Valid']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Teste para nome muito longo
    $response = $this->postJson('/api/boards', [
        'name' => str_repeat('a', 256),
        'columns' => [['name' => 'Valid']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Teste para coluna sem nome
    $response = $this->postJson('/api/boards', [
        'name' => 'Valid',
        'columns' => [['name' => '']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['columns.0.name']);

    // Teste para coluna com nome muito longo
    $response = $this->postJson('/api/boards', [
        'name' => 'Valid',
        'columns' => [['name' => str_repeat('a', 256)]],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['columns.0.name']);
});

test('board update validation', function (): void {
    $board = Board::factory()->create(['user_id' => $this->user->id]);
    $column = $board->columns()->create(['name' => 'Original']);

    $this->actingAs($this->user);

    // Teste para nome vazio
    $response = $this->putJson(route('api.boards.update', $board), [
        'name' => '',
        'columns' => [['id' => $column->id, 'name' => 'Valid']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Teste para coluna sem ID e sem nome
    $response = $this->putJson(route('api.boards.update', $board), [
        'name' => 'Valid',
        'columns' => [['name' => '']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['columns.0.name']);

    // Teste para coluna com ID inválido
    $response = $this->putJson(route('api.boards.update', $board), [
        'name' => 'Valid',
        'columns' => [['id' => 999, 'name' => 'Invalid']],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['columns.0.id']);
});
