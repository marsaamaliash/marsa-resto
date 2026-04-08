<?php

use App\Livewire\Holdings\Resto\Master\DashboardMaster;
use App\Livewire\Holdings\Resto\Master\Item\ItemTable;
use App\Livewire\Holdings\Resto\Master\Kategori\KategoriTable;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiTable;
use App\Livewire\Holdings\Resto\Master\Satuan\SatuanTable;
use App\Livewire\Holdings\Resto\Master\Vendor\VendorTable;
use App\Livewire\Holdings\Resto\Procurement\DashboardProcurement;
use Illuminate\Support\Facades\Route;

// ✅ Ubah prefix dan name di sini
Route::prefix('dashboard/resto')
    ->name('dashboard.resto.')
    ->group(function () {
        Route::get('/master-resto', DashboardMaster::class)->name('master');
        Route::get('/procurement', DashboardProcurement::class)->name('procurement');
        Route::get('/satuan', SatuanTable::class)->name('satuan');
        Route::get('/kategori', KategoriTable::class)->name('kategori');
        Route::get('/vendor', VendorTable::class)->name('vendor');
        Route::get('/lokasi', LokasiTable::class)->name('lokasi');
        Route::get('/item', ItemTable::class)->name('item');
        // Route::get('/pembayaran', RestoPembayaranDashboard::class)->name('pembayaran');
    });
