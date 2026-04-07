<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SSOVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        abort_unless($user, 401);

        // LOCK selalu block (bahkan super admin)
        abort_unless((int) $user->is_locked === 0, 403, 'Akun Anda terkunci');

        // SUPER ADMIN BYPASS
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Pastikan identity ada + aktif + tidak locked
        abort_unless(
            $user->isActive(),
            403,
            'Akun Anda tidak aktif / terkunci'
        );

        return $next($request);
    }
}
