<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::apiResource('boards', BoardController::class);

    Route::apiResource('columns', ColumnController::class);

    Route::get('/tags', [TagController::class, 'index']);
});
