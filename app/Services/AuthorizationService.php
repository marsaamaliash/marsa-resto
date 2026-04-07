<?php

namespace App\Services;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class AuthorizationService
{
    /* =====================================================
     | MODULE
     ===================================================== */

    public function canAccessModule(AuthUser $user, string $moduleCode): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return in_array($moduleCode, $user->modules(), true);
    }

    public function moduleAccessLevel(AuthUser $user, string $moduleCode): ?string
    {
        if ($user->isSuperAdmin()) {
            return 'full';
        }

        return $user->moduleAccessLevel($moduleCode);
    }

    public function canWriteModule(AuthUser $user, string $moduleCode): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->moduleAccessLevel($user, $moduleCode) === 'full';
    }

    /* =====================================================
     | PERMISSION
     ===================================================== */

    public function hasPermission(AuthUser $user, string $permissionCode): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission($permissionCode);
    }

    public function permissionRequiresApproval(string $permissionCode): bool
    {
        return (bool) DB::table('auth_permissions')
            ->where('code', $permissionCode)
            ->value('requires_approval');
    }
}
