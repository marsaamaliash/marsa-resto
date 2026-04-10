<?php

use App\Livewire\Holdings\Resto\CoreStock\DashboardCoreStock;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockItemTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockLocationTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMinimalTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMutationTable;
use App\Livewire\Holdings\Resto\Master\DashboardMaster;
use App\Livewire\Holdings\Resto\Master\Item\ItemTable;
use App\Livewire\Holdings\Resto\Master\Kategori\KategoriTable;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiTable;
use App\Livewire\Holdings\Resto\Master\Satuan\SatuanTable;
use App\Livewire\Holdings\Resto\Master\Vendor\VendorTable;
use App\Livewire\Holdings\Resto\Movement\DashboardMovement;
use App\Livewire\Holdings\Resto\Movement\Internal\MovementInternalTable;
use Illuminate\Support\Facades\Route;

// ✅ Ubah prefix dan name di sini
Route::prefix('dashboard/resto')
    ->name('dashboard.resto.')
    ->group(function () {
        Route::get('/master-resto', DashboardMaster::class)->name('master');
        Route::get('/core-stock', DashboardCoreStock::class)->name('core-stock');
        Route::get('/master-movement', DashboardMovement::class)->name('master-movement');

        Route::get('/satuan', SatuanTable::class)->name('satuan');
        Route::get('/kategori', KategoriTable::class)->name('kategori');
        Route::get('/vendor', VendorTable::class)->name('vendor');
        Route::get('/lokasi', LokasiTable::class)->name('lokasi');
        Route::get('/item', ItemTable::class)->name('item');

        Route::get('/stock-location', StockLocationTable::class)->name('stock-location');
        Route::get('/stock-item', StockItemTable::class)->name('stock-item');
        Route::get('/stock-minimal', StockMinimalTable::class)->name('stock-minimal');
        Route::get('/stock-mutation', StockMutationTable::class)->name('stock-mutation');

        Route::get('/movement-internal', MovementInternalTable::class)->name('movement-internal');
    });
