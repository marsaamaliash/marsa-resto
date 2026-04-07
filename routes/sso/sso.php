<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sso')
    ->name('sso.')
    ->middleware([
        'auth',
        'force.password.change',
        \App\Http\Middleware\AuthorizeModule::class.':00000',
    ])
    ->group(function () {

        // Dashboard SSO (opsional kalau mau URL /sso)
        Route::get('/', fn () => redirect()->route('dashboard.sso'))->name('home');

        // ✅ SSO Role
        Route::get('/roles', \App\Livewire\Auth\Sso\Roles\SsoRoleTable::class)
            ->name('roles.table')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_ROLE_VIEW');

        Route::get('/go', [\App\Http\Controllers\SSO\GoController::class, 'go'])
            ->name('go');

        // ✅ Menu Editor (auth_nav_items)
        Route::get('/nav-items', \App\Livewire\Auth\Sso\Nav\SsoNavItemTable::class)
            ->name('nav-items.table')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_NAV_VIEW');

        // Approval Inbox (tetap)
        Route::get('/approvals', \App\Livewire\Sso\Approvals\ApprovalInboxTable::class)
            ->name('approvals.inbox')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':APPROVAL_VIEW');

        // ✅ SSO Users (folder: resources/views/livewire/auth/sso)
        Route::get('/users', \App\Livewire\Auth\Sso\SsoUserTable::class)
            ->name('users.table')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_USER_VIEW');

        Route::get('/users/create', \App\Livewire\Auth\Sso\SsoUserCreate::class)
            ->name('users.create')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_USER_CREATE');

        Route::get('/users/{userId}', \App\Livewire\Auth\Sso\SsoUserShow::class)
            ->name('users.show')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_USER_VIEW');

        Route::get('/users/{userId}/edit', \App\Livewire\Auth\Sso\SsoUserEdit::class)
            ->name('users.edit')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_USER_UPDATE');

        Route::get('/users/{userId}/roles', \App\Livewire\Auth\Sso\SsoUserRoles::class)
            ->name('users.roles')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_USER_UPDATE');
    });
