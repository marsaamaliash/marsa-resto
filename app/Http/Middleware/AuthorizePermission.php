<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthorizePermission
{
    public function handle(Request $request, Closure $next, string $permissionCode)
    {
        $permissionCode = trim((string) $permissionCode);
        if ($permissionCode === '') {
            return $this->deny($request, 403, 'Forbidden (permission empty)');
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

        // ✅ lookup module_code dari permission (cached)
        $moduleCode = Cache::remember(
            "auth:perm:{$permissionCode}:module_code",
            now()->addHours(12),
            function () use ($permissionCode) {
                return DB::table('auth_permissions')
                    ->where('code', $permissionCode)
                    ->where('is_active', 1) // optional tapi bagus: permission non-aktif dianggap tidak valid
                    ->value('module_code');
            }
        );

        if (! $moduleCode) {
            return $this->deny($request, 403, "Permission not found or inactive: {$permissionCode}");
        }

        // ✅ module gate dulu (strict)
        if (! method_exists($u, 'hasModule') || ! $u->hasModule((string) $moduleCode)) {
            return $this->deny($request, 403, "Forbidden (module {$moduleCode})");
        }

        // ✅ permission gate
        if (! method_exists($u, 'hasPermission') || ! $u->hasPermission($permissionCode)) {
            return $this->deny($request, 403, "Forbidden (permission {$permissionCode})");
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
