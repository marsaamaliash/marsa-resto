<?php

use App\Livewire\Holdings\Resto\CoreStock\DashboardCoreStock;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockItemTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockLocationTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMinimalTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockMutationTable;
use App\Livewire\Holdings\Resto\CoreStock\Stock\StockRequestTable;
use App\Livewire\Holdings\Resto\Master\DashboardMaster;
use App\Livewire\Holdings\Resto\Master\Item\ItemTable;
use App\Livewire\Holdings\Resto\Master\Kategori\KategoriTable;
use App\Livewire\Holdings\Resto\Master\Lokasi\LokasiTable;
use App\Livewire\Holdings\Resto\Master\Satuan\SatuanTable;
use App\Livewire\Holdings\Resto\Master\Vendor\VendorTable;
use App\Livewire\Holdings\Resto\Movement\DashboardMovement;
use App\Livewire\Holdings\Resto\Movement\Internal\MovementInternalDetail;
use App\Livewire\Holdings\Resto\Movement\Internal\MovementInternalTable;
use App\Livewire\Holdings\Resto\Pos\Cashier;
use App\Livewire\Holdings\Resto\Pos\ChefKitchen;
use App\Livewire\Holdings\Resto\Pos\EmployeeLunch;
use App\Livewire\Holdings\Resto\Pos\EmployeeLunchReport;
use App\Livewire\Holdings\Resto\Pos\Menu\MenuShow;
use App\Livewire\Holdings\Resto\Pos\Menu\MenuTable;
use App\Livewire\Holdings\Resto\Pos\MenuPage;
use App\Livewire\Holdings\Resto\Pos\WaiterOrders;
use App\Livewire\Holdings\Resto\Procurement\DashboardProcurement;
use App\Livewire\Holdings\Resto\Procurement\DirectOrder\DirectOrderCreate;
use App\Livewire\Holdings\Resto\Procurement\DirectOrder\DirectOrderDetail;
use App\Livewire\Holdings\Resto\Procurement\DirectOrder\DirectOrderTable;
use App\Livewire\Holdings\Resto\Procurement\PurchaseOrder\PurchaseOrderCreate;
use App\Livewire\Holdings\Resto\Procurement\PurchaseOrder\PurchaseOrderDetail;
use App\Livewire\Holdings\Resto\Procurement\PurchaseOrder\PurchaseOrderTable;
use App\Livewire\Holdings\Resto\Procurement\PurchaseRequest\PurchaseRequestCreate;
use App\Livewire\Holdings\Resto\Procurement\PurchaseRequest\PurchaseRequestDetail;
use App\Livewire\Holdings\Resto\Procurement\PurchaseRequest\PurchaseRequestTable;
use App\Livewire\Holdings\Resto\Produksi\ProductionOrder\ProductionOrderCreate;
use App\Livewire\Holdings\Resto\Produksi\ProductionOrder\ProductionOrderShow;
use App\Livewire\Holdings\Resto\Produksi\ProductionOrder\ProductionOrderTable;
use App\Livewire\Holdings\Resto\Resep\DashboardResep;
use App\Livewire\Holdings\Resto\Resep\KonversiSatuan\KonversiSatuanTable;
use App\Livewire\Holdings\Resto\Resep\Menu\ResepMenuTable as MenuResepMenuTable;
use App\Livewire\Holdings\Resto\Resep\Recipe\RecipeShow;
use App\Livewire\Holdings\Resto\Resep\Recipe\RecipeTable;
use App\Livewire\Holdings\Resto\Resep\Repack\RepackTable;
use Illuminate\Support\Facades\Route;

// ✅ Ubah prefix dan name di sini
Route::prefix('dashboard/resto')
    ->name('dashboard.resto.')
    ->group(function () {
        Route::get('/master-resto', DashboardMaster::class)->name('master');
        Route::get('/core-stock', DashboardCoreStock::class)->name('core-stock');
        Route::get('/master-movement', DashboardMovement::class)->name('master-movement');
        Route::get('/resep', DashboardResep::class)->name('resep');

        Route::get('/menu', MenuTable::class)->name('menu');
        Route::get('/menu/{id}', MenuShow::class)->name('menu.detail');
        Route::get('/menu-pos', MenuPage::class)->name('menu-pos');
        Route::get('/employee-lunch', EmployeeLunch::class)->name('employee-lunch');
        Route::get('/employee-lunch/report', EmployeeLunchReport::class)->name('employee-lunch.report');

        Route::get('/chef', ChefKitchen::class)->name('chef');
        Route::get('/orders', WaiterOrders::class)->name('orders');
        Route::get('/cashier', Cashier::class)->name('cashier');

        Route::get('/satuan', SatuanTable::class)->name('satuan');
        Route::get('/kategori', KategoriTable::class)->name('kategori');
        Route::get('/vendor', VendorTable::class)->name('vendor');
        Route::get('/lokasi', LokasiTable::class)->name('lokasi');
        Route::get('/item', ItemTable::class)->name('item');

        Route::get('/stock-location', StockLocationTable::class)->name('stock-location');
        Route::get('/stock-item', StockItemTable::class)->name('stock-item');
        Route::get('/stock-minimal', StockMinimalTable::class)->name('stock-minimal');
        Route::get('/stock-mutation', StockMutationTable::class)->name('stock-mutation');
        Route::get('/stock-request', StockRequestTable::class)->name('stock-request');

        Route::get('/movement-internal', MovementInternalTable::class)->name('movement-internal');
        Route::get('/movement-internal/{id}', MovementInternalDetail::class)->name('movement-internal.detail');

        Route::get('/konversi-satuan', KonversiSatuanTable::class)->name('konversi-satuan');
        Route::get('/repack', RepackTable::class)->name('repack');

        Route::get('/resep-menu', MenuResepMenuTable::class)->name('resep-menu');

        Route::get('/procurement', DashboardProcurement::class)->name('procurement');
        Route::get('/purchase-request', PurchaseRequestTable::class)->name('purchase-request');
        Route::get('/purchase-request/create', PurchaseRequestCreate::class)->name('purchase-request.create');
        Route::get('/purchase-request/edit/{id}', PurchaseRequestCreate::class)->name('purchase-request.edit');
        Route::get('/purchase-request/revise/{id}', PurchaseRequestCreate::class)->name('purchase-request.revise');
        Route::get('/purchase-request/{id}', PurchaseRequestDetail::class)->name('purchase-request.detail');
        Route::get('/purchase-order', PurchaseOrderTable::class)->name('purchase-order');
        Route::get('/purchase-order/create', PurchaseOrderCreate::class)->name('purchase-order.create');
        Route::get('/purchase-order/{id}', PurchaseOrderDetail::class)->name('purchase-order.detail');
        Route::get('/direct-order', DirectOrderTable::class)->name('direct-order');
        Route::get('/direct-order/create', DirectOrderCreate::class)->name('direct-order.create');
        Route::get('/direct-order/{id}', DirectOrderDetail::class)->name('direct-order.detail');
        Route::get('/resep/recipe', RecipeTable::class)->name('resep.recipe');
        Route::get('/resep/recipe/{id}', RecipeShow::class)->name('resep.recipe.detail');
        Route::get('/resep/production', ProductionOrderTable::class)->name('resep.production');
        Route::get('/resep/production/create', ProductionOrderCreate::class)->name('resep.production.create');
        Route::get('/resep/production/{id}', ProductionOrderShow::class)->name('resep.production.detail');
    });
