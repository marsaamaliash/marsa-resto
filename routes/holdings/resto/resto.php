<?php

use App\Livewire\Holdings\Resto\DashboardMaster;
use Illuminate\Support\Facades\Route;

// ✅ Ubah prefix dan name di sini
Route::prefix('dashboard/resto')
    ->name('dashboard.resto.')
    ->group(function () {
        Route::get('/master-resto', DashboardMaster::class)->name('master');
        // Route::get('/menu', RestoMenuDashboard::class)->name('menu');
        // Route::get('/pembayaran', RestoPembayaranDashboard::class)->name('pembayaran');
    });