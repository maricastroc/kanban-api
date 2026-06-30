<?php

use App\Models\Board;
use App\Models\Tag;
use App\Models\User;
use App\Support\DemoWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('I should be able to register with valid data', function (): void {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('I should be able to register without name, email and password', function (): void {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('I should not be able to register with an existing email', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('I should be able to login with correct credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

test('I should only be able to login with valid email and password', function (): void {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('I should not be able to login with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'email' => 'The provided credentials are incorrect.',
        ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'email' => 'The provided credentials are incorrect.',
        ]);
});

test('I should be able to enter the demo workspace', function (): void {
    $response = $this->postJson('/api/demo-login');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);

    $this->assertDatabaseHas('users', ['email' => DemoWorkspace::EMAIL]);

    $user = User::firstWhere('email', DemoWorkspace::EMAIL);

    // The sample workspace was seeded with exactly one active board.
    expect(Board::where('user_id', $user->id)->count())->toBeGreaterThan(1);
    expect(Board::where('user_id', $user->id)->where('is_active', true)->count())->toBe(1);
    $this->assertDatabaseHas('boards', [
        'user_id' => $user->id,
        'name' => 'Platform Launch',
    ]);
});

test('demo login reseeds the workspace on every entry', function (): void {
    $this->postJson('/api/demo-login')->assertStatus(200);

    $user = User::firstWhere('email', DemoWorkspace::EMAIL);
    $seededBoards = Board::where('user_id', $user->id)->count();

    // Simulate a previous visitor wiping and vandalising the shared workspace.
    Board::where('user_id', $user->id)->delete();
    Board::create([
        'name' => 'Vandalised board',
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $this->postJson('/api/demo-login')->assertStatus(200);

    expect(Board::where('user_id', $user->id)->count())->toBe($seededBoards);
    expect(Board::where('user_id', $user->id)->where('name', 'Vandalised board')->exists())
        ->toBeFalse();
});

test('demo login sets a httpOnly auth cookie', function (): void {
    $response = $this->postJson('/api/demo-login');

    $cookie = collect($response->headers->getCookies())
        ->first(fn ($cookie): bool => $cookie->getName() === 'auth_token');

    expect($cookie)->not->toBeNull();
    expect($cookie->isHttpOnly())->toBeTrue();
});

test('the demo workspace is richly seeded for the portfolio', function (): void {
    $this->postJson('/api/demo-login')->assertStatus(200);

    $user = User::firstWhere('email', DemoWorkspace::EMAIL);

    expect(Tag::where('user_id', $user->id)->count())->toBe(8);

    $board = Board::where('user_id', $user->id)
        ->where('name', 'Platform Launch')
        ->first();

    // Every column carries at least five tasks so the demo board looks full.
    expect($board->columns)->toHaveCount(5);
    $board->columns->each(
        fn ($column) => expect($column->tasks->count())->toBeGreaterThanOrEqual(5)
    );
});
