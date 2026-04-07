<?php

use App\Livewire\Dashboard\CampusDashboard;
use App\Livewire\Dashboard\FinanceDashboard;
use App\Livewire\Dashboard\HqDashboard;
use App\Livewire\Dashboard\HrDashboard;
use App\Livewire\Dashboard\LmsMain;
use App\Livewire\Dashboard\MainDashboard;
use App\Livewire\Dashboard\ResortDashboard;
use App\Livewire\Dashboard\RestoDashboard;
use App\Livewire\Dashboard\RtDashboard;
use App\Livewire\Dashboard\SdmDashboard;
use App\Livewire\Dashboard\SiakadDashboard;
use App\Livewire\Dashboard\SsoDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->get('/dashboard', MainDashboard::class)
    ->name('dashboard');

Route::middleware(['auth'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/hq', HqDashboard::class)->name('hq');
        Route::get('/sdm', SdmDashboard::class)->name('sdm');
        Route::get('/hr', HrDashboard::class)->name('hr');
        Route::get('/rt', RtDashboard::class)->name('rt');
        Route::get('/finance', FinanceDashboard::class)->name('finance');
        Route::get('/resort', ResortDashboard::class)->name('resort');
        Route::get('/campus', CampusDashboard::class)->name('campus');
        Route::get('/resto', RestoDashboard::class)->name('resto');
        Route::get('/lms', LmsMain::class)->name('lms-main');
        Route::get('/siakad', SiakadDashboard::class)->name('siakad-dashboard');

        // ✅ SSO Dashboard (INI yang dipanggil route('dashboard.sso'))
        Route::get('/sso', SsoDashboard::class)
            ->name('sso')
            ->middleware([
                \App\Http\Middleware\AuthorizeModule::class.':00000',
                // tidak pakai authorize.permission di dashboard, tiap menu nanti gate sendiri
            ]);

    });
