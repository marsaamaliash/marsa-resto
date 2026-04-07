<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenGuard
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // dari auth:sanctum
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        // kalau user wajib ganti password, hanya boleh akses endpoint tertentu
        if ((int) ($user->must_change_password ?? 0) === 1) {
            $routeName = (string) optional($request->route())->getName();

            $allow = in_array($routeName, [
                'api.v1.me',
                'api.v1.auth.change-password',
                'api.v1.auth.logout',
            ], true);

            if (! $allow) {
                abort(403, 'Must change password');
            }
        }

        return $next($request);
    }
}
