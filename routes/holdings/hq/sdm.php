<?php

use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeCreate;
/*
|--------------------------------------------------------------------------
| HOLDING HQ → SDM
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| HR → EMPLOYEE
| Module Code : 01001
|--------------------------------------------------------------------------
*/
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeEdit;
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeShow;
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeTable;
use Illuminate\Support\Facades\Route;

Route::prefix('holdings/hq/sdm/hr')
    ->name('holdings.hq.sdm.hr.')
    ->middleware('authorize.module:01001')
    ->group(function () {

        // ENTRY MODULE (TABLE)
        Route::get('/', EmployeeTable::class)
            ->name('employee-table')
            ->middleware('authorize.permission:EMP_VIEW');

        // CREATE
        Route::get('/create', EmployeeCreate::class)
            ->name('employee-create')
            ->middleware('authorize.permission:EMP_CREATE');

        // EDIT (taruh di atas wildcard)
        Route::get('/{nip}/edit', EmployeeEdit::class)
            ->name('employee-edit')
            ->middleware('authorize.permission:EMP_UPDATE')
            ->where('nip', '[^/]+');

        // SHOW (wildcard)
        Route::get('/{nip}', EmployeeShow::class)
            ->name('employee-show')
            ->middleware('authorize.permission:EMP_VIEW')
            ->where('nip', '[^/]+');
    });

use App\Livewire\Holdings\Hq\Sdm\Hr\AbsensiManager;
use App\Livewire\Holdings\Hq\Sdm\Hr\AbsensiPwaManager;

Route::prefix('holdings/hq/sdm/hr/absensi')
    ->name('holdings.hq.sdm.hr.absensi.')
    ->middleware('authorize.module:01001')
    ->group(function () {
        Route::get('/', AbsensiManager::class)
            ->name('index')
            ->middleware('authorize.permission:ABS_VIEW');
    });

Route::prefix('holdings/hq/sdm/hr/absensi-pwa')
    ->name('holdings.hq.sdm.hr.absensi-pwa.')
    ->middleware('authorize.module:01001')
    ->group(function () {
        Route::get('/', AbsensiPwaManager::class)
            ->name('index')
            ->middleware('authorize.permission:ABS_VIEW');
    });

/*
|--------------------------------------------------------------------------
| GA → INVENTARIS
| Module Code : 01005
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Holdings\Hq\Sdm\Rt\Inventaris\InventarisPrintController;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisCreate;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisDeleteOutstanding;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisEdit;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisShow;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\InventarisTable;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\InvMasterIndex;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi\InvMasterLokasiDeleteOutstanding;
use App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi\InvMasterLokasiTable;

Route::prefix('holdings/hq/sdm/rt/inventaris')
    ->name('holdings.hq.sdm.rt.inventaris.')
    ->middleware('authorize.module:01005')
    ->group(function () {

        // ENTRY MODULE (TABLE)
        Route::get('/', InventarisTable::class)
            ->name('inventaris-table');

        // CREATE
        Route::get('/create', InventarisCreate::class)
            ->name('inventaris-create')
            ->middleware('authorize.permission:INV_CREATE');

        // PRINT (harus di atas wildcard)
        Route::get('/print-label', [InventarisPrintController::class, 'print'])
            ->name('inventaris-print-bulk');

        // EDIT (taruh di atas wildcard)
        Route::get('/{kode_label}/edit', InventarisEdit::class)
            ->name('inventaris-edit');
        // ->middleware('authorize.permission:INV_UPDATE');

        // SHOW (wildcard paling bawah)
        Route::get('/{kode_label}', InventarisShow::class)
            ->where('kode_label', '^[A-Za-z0-9]+(\.[A-Za-z0-9]+)+$')
            ->name('inventaris-show');

        // DELETE OUTSTANDING (approval)
        Route::get('/delete-outstanding', InventarisDeleteOutstanding::class)
            ->name('inventaris-delete-outstanding')
            ->middleware('authorize.permission:INV_DELETE_APPROVE');

        // MASTER INDEX
        Route::prefix('master')->name('master.')->group(function () {

            Route::get('/', InvMasterIndex::class)
                ->name('index');

            Route::get('/lokasi', InvMasterLokasiTable::class)
                ->name('lokasi.table');

            Route::get('/lokasi/delete-outstanding', InvMasterLokasiDeleteOutstanding::class)
                ->name('lokasi.delete-outstanding');
        });
    });
