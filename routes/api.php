<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\MoveTaskController;
use App\Http\Controllers\ReorderTaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/boards/active', [BoardController::class, 'active']);

    Route::patch('/boards/{board}/activate', [BoardController::class, 'setActive']);

    Route::apiResource('boards', BoardController::class)->scoped(['board' => 'uuid']);

    Route::apiResource('columns', ColumnController::class)->scoped(['column' => 'uuid']);

    Route::apiResource('tasks', TaskController::class)->scoped(['task' => 'uuid']);

    Route::apiResource('tags', TagController::class)->scoped(['tag' => 'uuid']);

    Route::apiResource('subtasks', SubtaskController::class)->only(['store', 'update', 'destroy'])->scoped(['subtask' => 'uuid']);

    Route::put('/tasks/{task}/reorder', [ReorderTaskController::class, '__invoke']);

    Route::put('/tasks/{task}/move', [MoveTaskController::class, '__invoke']);

    Route::apiResource('/tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/logout', [AuthController::class, 'logout']);
});
