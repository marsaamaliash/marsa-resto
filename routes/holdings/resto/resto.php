<?php

use App\Livewire\Holdings\Resto\CoreStock\DashboardCoreStock;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockItem;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockLocation;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMinimal;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMutation;
use App\Livewire\Holdings\Resto\Master\DashboardMaster;
use App\Livewire\Holdings\Resto\Master\Item\ItemTable;
use App\Livewire\Holdings\Resto\Master\Kategori\KategoriTable;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiTable;
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
        Route::get('/lokasi', LokasiTable::class)->name('lokasi');
        Route::get('/item', ItemTable::class)->name('item');
        Route::get('/core-stock', DashboardCoreStock::class)->name('core-stock');
        Route::get('/stock-location', StockLocation::class)->name('stock-location');
        Route::get('/stock-item', StockItem::class)->name('stock-item');
        Route::get('/stock-minimal', StockMinimal::class)->name('stock-minimal');
        Route::get('/stock-mutation', StockMutation::class)->name('stock-mutation');
        // Route::get('/pembayaran', RestoPembayaranDashboard::class)->name('pembayaran');
    });
