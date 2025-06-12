<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::apiResource('boards', BoardController::class);

    Route::apiResource('columns', ColumnController::class);

    Route::apiResource('tasks', TaskController::class);

    Route::apiResource('subtasks', SubtaskController::class)->only(['store', 'update', 'destroy']);

    Route::get('/tags', [TagController::class, 'index']);
});
