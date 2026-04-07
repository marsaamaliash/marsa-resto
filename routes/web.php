<?php

use App\Http\Controllers\SSO\QRController;
use App\Livewire\Bod\EmployeeDashboard;
use App\Livewire\Bod\FinanceDashboard;
use App\Livewire\Bod\InventarisDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('dashboard'))
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| AUTH (LIVEWIRE ONLY)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| DASHBOARD (ENTRY POINT)
|--------------------------------------------------------------------------
*/
require __DIR__.'/dashboard.php';

/*
|--------------------------------------------------------------------------
| GLOBAL UI + MODULES (SSO ZONE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'force.password.change', 'auth.membership'])->group(function () {

    // Utilities
    Route::get('/sso/generate-qr', [QRController::class, 'generate'])
        ->name('sso.qr.generate');

    // ✅ CHANGE PASSWORD harus bebas authorize.module/permission
    Route::get('/sso/change-password', \App\Livewire\Auth\ForcePasswordChange::class)
        ->name('sso.password.change');

    Route::get('/bod/employees', EmployeeDashboard::class)->name('bod.employees.dashboard');
    Route::get('/bod/inventaris', InventarisDashboard::class)->name('bod.inventaris.dashboard');
    Route::get('/bod/finance', FinanceDashboard::class)->name('bod.finance.dashboard');

    // ✅ SSO Governance modules (yang butuh authorize.module/permission) tetap di file sso/sso.php
    require __DIR__.'/sso/sso.php';

    // Modules lain
    require __DIR__.'/holdings/hq/sdm.php';
    require __DIR__.'/holdings/hq/finance.php';
    require __DIR__.'/holdings/campus/campus.php';
    require __DIR__.'/holdings/resto/resto.php';
});

/*
|--------------------------------------------------------------------------
| PROFILE (LIVEWIRE, NO BREEZE CONTROLLER)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'force.password.change'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/', \App\Livewire\Auth\Profile\ProfilePage::class)->name('edit');
    });
