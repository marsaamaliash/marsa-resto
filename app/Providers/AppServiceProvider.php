<?php

namespace App\Providers;

use App\Models\Auth\AuthPersonalAccessToken;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(AuthPersonalAccessToken::class);
        /**
         * MODULE DIRECTIVE
         *
         * @module('01005')
         */
        Blade::if('module', function (string $code) {
            if (! auth()->check()) {
                return false;
            }

            return app(AuthorizationService::class)
                ->canAccessModule(auth()->user(), $code);
        });

        /**
         * PERMISSION DIRECTIVE
         *
         * @permission('INV_DELETE')
         */
        Blade::if('permission', function (string $code) {
            if (! auth()->check()) {
                return false;
            }

            return app(AuthorizationService::class)
                ->hasPermission(auth()->user(), $code);
        });
    }
}
