<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->board = Board::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

test('I should be able to reorder a column within the board', function (): void {
    $columns = Column::factory()->count(3)->create([
        'board_id' => $this->board->id,
    ])->sortBy('order')->values();

    $columnToMove = $columns[0];
    $newPosition = 2;

    $response = $this->patchJson("/api/columns/{$columnToMove->id}/reorder", [
        'new_order' => $newPosition,
    ]);

    $response->assertStatus(200)->assertJson(['success' => true]);

    $this->assertDatabaseHas('columns', [
        'id' => $columnToMove->id,
        'order' => $newPosition,
    ]);

    expect($columns[1]->fresh()->order)->toBe(1);
    expect($columns[2]->fresh()->order)->toBe(3);
});

test('Moving a column to a higher position updates the others', function (): void {
    $columns = Column::factory()->count(3)->create([
        'board_id' => $this->board->id,
    ])->sortBy('order')->values();

    $columnToMove = $columns[2];
    $newPosition = 1;

    $response = $this->patchJson("/api/columns/{$columnToMove->id}/reorder", [
        'new_order' => $newPosition,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('columns', [
        'id' => $columnToMove->id,
        'order' => $newPosition,
    ]);

    expect($columns[0]->fresh()->order)->toBe(2);
    expect($columns[1]->fresh()->order)->toBe(3);
});

test('Reordering a column to the same position does nothing', function (): void {
    $column = Column::factory()->create([
        'board_id' => $this->board->id,
        'order' => 1,
    ]);

    $response = $this->patchJson("/api/columns/{$column->id}/reorder", [
        'new_order' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Column already in correct position.']);
});

test('I should not be able to reorder another user\'s column', function (): void {
    $otherUser = User::factory()->create();
    $foreignColumn = Column::factory()->create([
        'board_id' => Board::factory()->create(['user_id' => $otherUser->id]),
    ]);

    $response = $this->patchJson("/api/columns/{$foreignColumn->id}/reorder", [
        'new_order' => 1,
    ]);

    $response->assertStatus(403);
});
