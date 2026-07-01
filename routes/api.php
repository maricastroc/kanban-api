<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BulkReorderSubtaskController;
use App\Http\Controllers\MoveTaskController;
use App\Http\Controllers\ReorderColumnController;
use App\Http\Controllers\ReorderTaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ToggleTaskCompletionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => ['user' => $request->user()]);

    Route::get('/boards/active', [BoardController::class, 'active']);

    Route::patch('/boards/{board}/activate', [BoardController::class, 'setActive']);

    Route::apiResource('boards', BoardController::class);

    Route::apiResource('tasks', TaskController::class)->except(['show']);

    Route::post('/tasks/{task}/tags/{tag}', [TagController::class, 'attachToTask'])->name('tasks.tags.attach');

    Route::delete('/tasks/{task}/tags/{tag}', [TagController::class, 'detachFromTask'])->name('tasks.tags.detach');

    Route::patch('/subtasks/{subtask}/toggle-completion', [SubtaskController::class, 'toggleCompletion']);

    Route::patch('/subtasks/reorder', BulkReorderSubtaskController::class);

    Route::patch('/tasks/{task}/reorder', [ReorderTaskController::class, '__invoke']);

    Route::patch('/tasks/{task}/move', [MoveTaskController::class, '__invoke']);

    Route::patch('/tasks/{task}/toggle-completion', ToggleTaskCompletionController::class);

    Route::patch('/columns/{column}/reorder', ReorderColumnController::class);

    Route::apiResource('/tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/logout', [AuthController::class, 'logout']);
});
