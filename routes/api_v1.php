<?php

use App\Http\Controllers\Api\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/token', [AuthTokenController::class, 'issue'])->name('auth.token');
    Route::post('/auth/refresh', [AuthTokenController::class, 'refresh'])->name('auth.refresh');

    Route::middleware(['auth:sanctum', 'auth.membership', 'api.token'])->group(function () {
        Route::post('/auth/logout', [AuthTokenController::class, 'logout'])->name('auth.logout');

        Route::get('/me', [AuthTokenController::class, 'me'])->name('me');
        Route::get('/modules', [AuthTokenController::class, 'modules'])->name('modules');

        Route::post('/auth/change-password', [AuthTokenController::class, 'changePassword'])
            ->name('auth.change-password')
            ->middleware('ability:password:change');
    });
});
