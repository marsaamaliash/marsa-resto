<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthorizeModule
{
    /**
     * Usage:
     *   ->middleware(AuthorizeModule::class . ':01001')
     *   ->middleware(AuthorizeModule::class . ':01001,full')
     */
    public function handle(Request $request, Closure $next, string $moduleCode, string $minAccess = 'view')
    {
        $moduleCode = trim((string) $moduleCode);
        if ($moduleCode === '') {
            return $this->deny($request, 403, 'Forbidden (module empty)');
        }

        $u = auth()->user();
        if (! $u) {
            return $this->deny($request, 401, 'Unauthenticated');
        }

        if ((int) ($u->is_locked ?? 0) === 1) {
            return $this->deny($request, 423, 'User locked');
        }

        if (method_exists($u, 'isActive') && ! $u->isActive()) {
            return $this->deny($request, 403, 'User inactive');
        }

        // super admin bypass
        if (method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin()) {
            return $next($request);
        }

        // ✅ pastikan module terdaftar & aktif (cached)
        $isModuleActive = Cache::remember(
            "auth:module:{$moduleCode}:is_active",
            now()->addHours(12),
            function () use ($moduleCode) {
                return (int) DB::table('auth_modules')
                    ->where('code', $moduleCode)
                    ->value('is_active') === 1;
            }
        );

        if (! $isModuleActive) {
            return $this->deny($request, 403, "Module not found or inactive: {$moduleCode}");
        }

        // ✅ module gate
        if (! method_exists($u, 'hasModule') || ! $u->hasModule($moduleCode)) {
            return $this->deny($request, 403, "Forbidden (module {$moduleCode})");
        }

        // ✅ optional access level gate
        $minAccess = strtolower(trim($minAccess));
        if ($minAccess === 'full') {
            $lvl = method_exists($u, 'moduleAccessLevel') ? $u->moduleAccessLevel($moduleCode) : null;
            if ($lvl !== 'full') {
                return $this->deny($request, 403, "Forbidden (module {$moduleCode} requires full)");
            }
        }

        return $next($request);
    }

    protected function deny(Request $request, int $status, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['ok' => false, 'message' => $message], $status);
        }

        abort($status, $message);
    }
}
