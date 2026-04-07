<?php

use Illuminate\Support\Facades\Route;

// use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeTable;
// use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeCreate;
// use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeEdit;
// use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeShow;

// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisTable;
// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisShow;
// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisEdit;
// use App\Http\Controllers\Holdings\Hq\Sdm\Rt\Inventaris\InventarisPrintController;

// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi\InvMasterLokasiDeleteOutstanding;

// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\InvMasterLokasiOutstanding;
// use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\InvMasterIndex;

/*
|--------------------------------------------------------------------------
| FINANCE
| Module Code : 01003
|--------------------------------------------------------------------------
*/
Route::prefix('holdings/hq/finance')
    ->name('holdings.hq.finance.')
    ->middleware('authorize.module:01003')
    ->group(function () {

        // ENTRY MODULE (TABLE)
        // Route::get('/', InventarisTable::class)
        //     ->name('inventaris-table');

        // Route::get('/create', \App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisCreate::class)
        //     ->name('inventaris-create')
        //     ->middleware('authorize.permission:INV_CREATE');

        // EDIT (taruh di atas wildcard)
        // Route::get('/{kode_label}/edit', InventarisEdit::class)
        //     ->name('inventaris-edit');
        // ->middleware('authorize.permission:INV_UPDATE');

        // WILDCARD taruh paling bawah + constraint(regex '^[A-Za-z0-9]+(\.[A-Za-z0-9]+)+$')
        // Route::get('/{kode_label}', InventarisShow::class)
        //     ->where('kode_label', '^[A-Za-z0-9]+(\.[A-Za-z0-9]+)+$')
        //     ->name('inventaris-show');

        // Route::get('/delete-outstanding', \App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisDeleteOutstanding::class)
        //     ->name('inventaris-delete-outstanding')
        //     ->middleware('authorize.permission:INV_DELETE_APPROVE');

        // ✅ MASTER INDEX (group page)
        Route::prefix('master')->name('master.')->group(function () {

            // Route::get('/', \App\Livewire\Holdings\Hq\Finance\Master\InvMasterIndex::class)
            //     ->name('index');

            // MASTER ACCOUNT (truth page)
            Route::get('/', \App\Livewire\Holdings\Hq\Finance\Master\FinMasterAccountTable::class)
                ->name('account.table');

            // OUTSTANDING APPROVAL (MASTER LOKASI)
            // Route::get('/lokasi/delete-outstanding', \App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi\InvMasterLokasiDeleteOutstanding::class)
            //         ->name('lokasi.delete-outstanding');
        });

    });
