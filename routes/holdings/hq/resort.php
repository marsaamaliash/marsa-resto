<?php

use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeCreate;
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeEdit;
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeShow;
use App\Livewire\Holdings\Hq\Sdm\Hr\EmployeeTable;
use Illuminate\Support\Facades\Route;

// Tambahkan AbsensiTable, InventarisTable jika tersedia

/*
|--------------------------------------------------------------------------
| SDM Holding HQ Routes (Livewire-native, CRUD only)
|--------------------------------------------------------------------------
*/

Route::prefix('holdings/hq/sdm/hr')->name('holdings.hq.sdm.hr.')->group(function () {
    Route::get('/', EmployeeTable::class)->name('employee-table');
    Route::get('/create', EmployeeCreate::class)->name('employee-create');
    Route::get('/{nip}', EmployeeShow::class)->name('employee-show');
    Route::get('/{nip}/edit', EmployeeEdit::class)->name('employee-edit');
});
