<?php

use App\Http\Middleware\AuthorizeModule;
use App\Http\Middleware\AuthorizePermission;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\SSOVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sso.verified' => SSOVerified::class,
            'authorize.module' => AuthorizeModule::class,
            'authorize.permission' => AuthorizePermission::class,

            // ✅ sanctum abilities middleware (sesuai docs)
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'api.token' => \App\Http\Middleware\ApiTokenGuard::class,

            // ✅ force password change (web)
            'force.password.change' => ForcePasswordChange::class,

            'auth.membership' => \App\Http\Middleware\SetAuthMembership::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
