<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\Auth\LoginForm;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH ENTRY (LIVEWIRE)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginForm::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| AUTH ACTIONS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout (AMAN tetap controller)
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
