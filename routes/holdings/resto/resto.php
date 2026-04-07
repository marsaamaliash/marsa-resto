<?php

use App\Livewire\Holdings\Resto\DashboardMaster;
use App\Livewire\Holdings\Resto\Master\Kategori\KategoriTable;
use App\Livewire\Holdings\Resto\Master\Satuan\SatuanTable;
use App\Livewire\Holdings\Resto\Master\Vendor\VendorTable;
use Illuminate\Support\Facades\Route;

// ✅ Ubah prefix dan name di sini
Route::prefix('dashboard/resto')
    ->name('dashboard.resto.')
    ->group(function () {
        Route::get('/master-resto', DashboardMaster::class)->name('master');
        Route::get('/satuan', SatuanTable::class)->name('satuan');
        Route::get('/kategori', KategoriTable::class)->name('kategori');
        Route::get('/vendor', VendorTable::class)->name('vendor');
        // Route::get('/pembayaran', RestoPembayaranDashboard::class)->name('pembayaran');
    });