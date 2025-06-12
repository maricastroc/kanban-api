<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserSyncController;

Route::get('/', fn (): array => [config('app.name')]);

Route::prefix('api')->name('api.')->group(function (): void {
    Route::middleware('nextauth')->get('/tags', [TagController::class, 'index']);

    Route::post('/sync-user', UserSyncController::class);
});
