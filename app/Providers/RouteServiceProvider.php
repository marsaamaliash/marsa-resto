<?php

namespace App\Providers;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\Quiz;
use App\Models\Holdings\Campus\Student;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->configureRateLimiting();

        /*
        |--------------------------------------------------------------------------
        | Global Route Model Binding
        |--------------------------------------------------------------------------
        */
        Route::model('room', LmsRoom::class);
        Route::model('quiz', Quiz::class);
        Route::model('student', Student::class);

        $this->routes(function () {

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // ❌ JANGAN load holdings di sini
            // ❌ web.php adalah single source of truth
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
