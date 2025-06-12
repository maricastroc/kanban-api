<?php

declare(strict_types = 1);

use App\Http\Controllers\HabitController;
use App\Http\Controllers\HabitLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;

Route::get('/', fn (): array => [config('app.name')]);

Route::prefix('api')->name('api.')->group(function (): void {
    Route::get('/tags', [TagController::class, 'index']);
});