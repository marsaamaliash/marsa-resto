<?php

namespace App\Services\Auth;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class AccessCloner
{
    /**
     * Clone access dari $sourceUserId ke $targetUserId.
     *
     * options:
     * - mode: replace|merge
     * - preset: all|roles_only|overrides_only
     * - scope_strategy: keep|rebase_to_target|to_global
     * - clone_roles: bool
     * - clone_module_overrides: bool
     * - clone_permission_overrides: bool
     * - only_active_overrides: bool
     * - actor_user_id: int
     */
    public static function cloneOne(int $sourceUserId, int $targetUserId, array $options = []): array
    {
        if ($sourceUserId <= 0 || $targetUserId <= 0) {
            return ['ok' => false, 'message' => 'Invalid user id'];
        }
        if ($sourceUserId === $targetUserId) {
            return ['ok' => false, 'message' => 'Source dan target sama'];
        }

        $mode = (string) ($options['mode'] ?? 'replace');
        $mode = in_array($mode, ['replace', 'merge'], true) ? $mode : 'replace';

        $preset = (string) ($options['preset'] ?? 'all');
        $preset = in_array($preset, ['all', 'roles_only', 'overrides_only'], true) ? $preset : 'all';

        $scopeStrategy = (string) ($options['scope_strategy'] ?? 'rebase_to_target');
        $scopeStrategy = in_array($scopeStrategy, ['keep', 'rebase_to_target', 'to_global'], true)
            ? $scopeStrategy
            : 'rebase_to_target';

        // base flags (advanced)
        $cloneRoles = (bool) ($options['clone_roles'] ?? true);
        $cloneModOv = (bool) ($options['clone_module_overrides'] ?? true);
        $clonePermOv = (bool) ($options['clone_permission_overrides'] ?? true);
        $onlyActive = (bool) ($options['only_active_overrides'] ?? true);

        // preset override flags
        if ($preset === 'roles_only') {
            $cloneRoles = true;
            $cloneModOv = false;
            $clonePermOv = false;
        } elseif ($preset === 'overrides_only') {
            $cloneRoles = false;
            $cloneModOv = true;
            $clonePermOv = true;
        }

        $actorId = (int) ($options['actor_user_id'] ?? 0);
        $now = now();

        // source exists?
        if (! DB::table('auth_users')->where('id', $sourceUserId)->exists()) {
            return ['ok' => false, 'message' => "Source user tidak ditemukan: {$sourceUserId}"];
        }

        // target exists?
        $tgtRow = DB::table('auth_users')->where('id', $targetUserId)->first(['id', 'is_super_admin']);
        if (! $tgtRow) {
            return ['ok' => false, 'message' => "Target user tidak ditemukan: {$targetUserId}"];
        }

        // scope target (untuk rebase)
        $targetScope = self::getUserScope($targetUserId);

        // =========================
        // Ambil data source (di luar tx)
        // =========================
        $srcRoleIds = [];
        if ($cloneRoles) {
            $srcRoleIds = DB::table('auth_user_roles')
                ->where('auth_user_id', $sourceUserId)
                ->pluck('role_id')
                ->map(fn ($x) => (int) $x)
                ->unique()
                ->values()
                ->toArray();
        }

        $srcModuleOverrides = [];
        if ($cloneModOv) {
            $q = DB::table('auth_user_module_overrides')->where('auth_user_id', $sourceUserId);
            if ($onlyActive) {
                $q->where('is_active', 1);
            }

            $srcModuleOverrides = $q->get([
                'module_code', 'effect', 'access_level', 'scope_type',
                'scope_holding_id', 'scope_department_id', 'scope_division_id',
                'reason', 'is_active',
            ])->map(fn ($r) => [
                'module_code' => (string) $r->module_code,
                'effect' => (string) $r->effect,
                'access_level' => (string) ($r->access_level ?? 'view'),
                'scope_type' => (string) ($r->scope_type ?? 'global'),
                'scope_holding_id' => $r->scope_holding_id !== null ? (int) $r->scope_holding_id : null,
                'scope_department_id' => $r->scope_department_id !== null ? (int) $r->scope_department_id : null,
                'scope_division_id' => $r->scope_division_id !== null ? (int) $r->scope_division_id : null,
                'reason' => (string) ($r->reason ?? ''),
                'is_active' => (int) ($r->is_active ?? 1),
            ])->toArray();
        }

        $srcPermissionOverrides = [];
        if ($clonePermOv) {
            $q = DB::table('auth_user_permission_overrides')->where('auth_user_id', $sourceUserId);
            if ($onlyActive) {
                $q->where('is_active', 1);
            }

            $srcPermissionOverrides = $q->get([
                'permission_code', 'effect', 'scope_type',
                'scope_holding_id', 'scope_department_id', 'scope_division_id',
                'reason', 'is_active',
            ])->map(fn ($r) => [
                'permission_code' => (string) $r->permission_code,
                'effect' => (string) $r->effect,
                'scope_type' => (string) ($r->scope_type ?? 'global'),
                'scope_holding_id' => $r->scope_holding_id !== null ? (int) $r->scope_holding_id : null,
                'scope_department_id' => $r->scope_department_id !== null ? (int) $r->scope_department_id : null,
                'scope_division_id' => $r->scope_division_id !== null ? (int) $r->scope_division_id : null,
                'reason' => (string) ($r->reason ?? ''),
                'is_active' => (int) ($r->is_active ?? 1),
            ])->toArray();
        }

        // =========================
        // Filter master data (optional safety)
        // =========================
        if ($cloneModOv && ! empty($srcModuleOverrides)) {
            $codes = array_values(array_unique(array_map(fn ($x) => (string) $x['module_code'], $srcModuleOverrides)));
            $valid = DB::table('auth_modules')
                ->whereIn('code', $codes)
                ->pluck('code')
                ->map(fn ($x) => (string) $x)
                ->toArray();

            $validSet = array_fill_keys($valid, true);
            $srcModuleOverrides = array_values(array_filter(
                $srcModuleOverrides,
                fn ($o) => isset($validSet[(string) $o['module_code']])
            ));
        }

        if ($clonePermOv && ! empty($srcPermissionOverrides)) {
            $codes = array_values(array_unique(array_map(fn ($x) => (string) $x['permission_code'], $srcPermissionOverrides)));
            $valid = DB::table('auth_permissions')
                ->whereIn('code', $codes)
                ->pluck('code')
                ->map(fn ($x) => (string) $x)
                ->toArray();

            $validSet = array_fill_keys($valid, true);
            $srcPermissionOverrides = array_values(array_filter(
                $srcPermissionOverrides,
                fn ($o) => isset($validSet[(string) $o['permission_code']])
            ));
        }

        $stats = [
            'roles' => ['inserted' => 0],
            'module_overrides' => ['upserted' => 0],
            'permission_overrides' => ['upserted' => 0],
        ];

        try {
            DB::transaction(function () use (
                $mode, $actorId, $now,
                $targetUserId,
                $cloneRoles, $cloneModOv, $clonePermOv,
                $srcRoleIds, $srcModuleOverrides, $srcPermissionOverrides,
                $scopeStrategy, $targetScope,
                &$stats
            ) {
                // lock target row
                DB::table('auth_users')->where('id', $targetUserId)->lockForUpdate()->first();

                // ===== REPLACE mode: bersihkan dulu sesuai item yang diclone =====
                if ($mode === 'replace') {
                    if ($cloneRoles) {
                        DB::table('auth_user_roles')->where('auth_user_id', $targetUserId)->delete();
                    }
                    if ($cloneModOv) {
                        DB::table('auth_user_module_overrides')->where('auth_user_id', $targetUserId)->delete();
                    }
                    if ($clonePermOv) {
                        DB::table('auth_user_permission_overrides')->where('auth_user_id', $targetUserId)->delete();
                    }
                }

                // ===== ROLES =====
                if ($cloneRoles) {
                    if (! empty($srcRoleIds)) {
                        $rows = array_map(fn ($rid) => [
                            'auth_user_id' => $targetUserId,
                            'role_id' => (int) $rid,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $srcRoleIds);

                        DB::table('auth_user_roles')->insertOrIgnore($rows);
                        $stats['roles']['inserted'] = count($rows);
                    } else {
                        $stats['roles']['inserted'] = 0;
                    }
                }

                // ===== MODULE OVERRIDES =====
                if ($cloneModOv && ! empty($srcModuleOverrides)) {
                    foreach ($srcModuleOverrides as $o) {
                        $scope = self::remapScope($o, $scopeStrategy, $targetScope);

                        self::upsertModuleOverride(
                            $targetUserId,
                            (string) $o['module_code'],
                            (string) $o['effect'],
                            (string) ($o['access_level'] ?? 'view'),
                            $scope,
                            (int) ($o['is_active'] ?? 1),
                            (string) ($o['reason'] ?? ''),
                            $actorId,
                            $now
                        );

                        $stats['module_overrides']['upserted']++;
                    }
                }

                // ===== PERMISSION OVERRIDES =====
                if ($clonePermOv && ! empty($srcPermissionOverrides)) {
                    foreach ($srcPermissionOverrides as $o) {
                        $scope = self::remapScope($o, $scopeStrategy, $targetScope);

                        self::upsertPermissionOverride(
                            $targetUserId,
                            (string) $o['permission_code'],
                            (string) $o['effect'],
                            $scope,
                            (int) ($o['is_active'] ?? 1),
                            (string) ($o['reason'] ?? ''),
                            $actorId,
                            $now
                        );

                        $stats['permission_overrides']['upserted']++;
                    }
                }
            });

            // ✅ clear cache target
            self::syncProvisioningUnlockIfEligible($targetUserId);

            if ($u = AuthUser::find($targetUserId)) {
                $u->clearAuthCache();
            }

            return [
                'ok' => true,
                'message' => 'cloned',
                'stats' => $stats,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private static function syncProvisioningUnlockIfEligible(int $userId): void
    {
        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $userId)
            ->first(['u.is_super_admin', 'u.is_locked', 'u.last_login_at', 'i.is_active']);

        if (! $row) {
            return;
        }

        // super admin jangan disentuh
        if ((int) ($row->is_super_admin ?? 0) === 1) {
            return;
        }

        // hanya auto-unlock akun provisioning (belum pernah login)
        if ((int) ($row->is_locked ?? 0) !== 1) {
            return;
        }
        if ($row->last_login_at !== null) {
            return;
        }

        if ((int) ($row->is_active ?? 0) !== 1) {
            return;
        }

        $hasRole = DB::table('auth_user_roles')->where('auth_user_id', $userId)->exists();

        $hasAllowModOv = DB::table('auth_user_module_overrides')
            ->where('auth_user_id', $userId)
            ->where('is_active', 1)
            ->where('effect', 'allow')
            ->exists();

        $hasAllowPermOv = DB::table('auth_user_permission_overrides')
            ->where('auth_user_id', $userId)
            ->where('is_active', 1)
            ->where('effect', 'allow')
            ->exists();

        if (! ($hasRole || $hasAllowModOv || $hasAllowPermOv)) {
            return;
        }

        DB::table('auth_users')
            ->where('id', $userId)
            ->update(['is_locked' => 0, 'updated_at' => now()]);
    }

    private static function getUserScope(int $userId): array
    {
        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $userId)
            ->first(['i.holding_id', 'i.department_id', 'i.division_id']);

        return [
            'holding_id' => (int) ($row->holding_id ?? 0),
            'department_id' => (int) ($row->department_id ?? 0),
            'division_id' => (int) ($row->division_id ?? 0),
        ];
    }

    /**
     * Normalize scope:
     * - scope_type harus: global|holding|department|division
     * - hanya 1 scope_id yang boleh terisi sesuai scope_type
     * - kalau id wajib kosong -> fallback global
     */
    private static function normalizeScope(array $scope): array
    {
        $type = (string) ($scope['scope_type'] ?? 'global');
        $type = in_array($type, ['global', 'holding', 'department', 'division'], true) ? $type : 'global';

        $h = isset($scope['scope_holding_id']) ? (is_null($scope['scope_holding_id']) ? null : (int) $scope['scope_holding_id']) : null;
        $d = isset($scope['scope_department_id']) ? (is_null($scope['scope_department_id']) ? null : (int) $scope['scope_department_id']) : null;
        $v = isset($scope['scope_division_id']) ? (is_null($scope['scope_division_id']) ? null : (int) $scope['scope_division_id']) : null;

        if ($type === 'global') {
            return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
        }

        if ($type === 'holding') {
            if (! $h || $h <= 0) {
                return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
            }

            return ['scope_type' => 'holding', 'scope_holding_id' => $h, 'scope_department_id' => null, 'scope_division_id' => null];
        }

        if ($type === 'department') {
            if (! $d || $d <= 0) {
                return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
            }

            return ['scope_type' => 'department', 'scope_holding_id' => null, 'scope_department_id' => $d, 'scope_division_id' => null];
        }

        // division
        if (! $v || $v <= 0) {
            return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
        }

        return ['scope_type' => 'division', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => $v];
    }

    private static function scopeTargetId(array $scope): int
    {
        $t = (string) ($scope['scope_type'] ?? 'global');

        if ($t === 'holding') {
            return (int) ($scope['scope_holding_id'] ?? 0);
        }
        if ($t === 'department') {
            return (int) ($scope['scope_department_id'] ?? 0);
        }
        if ($t === 'division') {
            return (int) ($scope['scope_division_id'] ?? 0);
        }

        return 0; // global
    }

    /**
     * Remap scope source -> target sesuai strategy.
     */
    private static function remapScope(array $src, string $strategy, array $targetScope): array
    {
        $srcType = (string) ($src['scope_type'] ?? 'global');

        $srcScope = [
            'scope_type' => ($srcType !== '' ? $srcType : 'global'),
            'scope_holding_id' => $src['scope_holding_id'] ?? null,
            'scope_department_id' => $src['scope_department_id'] ?? null,
            'scope_division_id' => $src['scope_division_id'] ?? null,
        ];

        if ($strategy === 'keep') {
            return self::normalizeScope($srcScope);
        }

        if ($strategy === 'to_global') {
            return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
        }

        // rebase_to_target:
        if ($srcType === 'global' || $srcType === '' || $srcType === null) {
            return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
        }

        if ((int) ($targetScope['division_id'] ?? 0) > 0) {
            return ['scope_type' => 'division', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => (int) $targetScope['division_id']];
        }

        if ((int) ($targetScope['department_id'] ?? 0) > 0) {
            return ['scope_type' => 'department', 'scope_holding_id' => null, 'scope_department_id' => (int) $targetScope['department_id'], 'scope_division_id' => null];
        }

        if ((int) ($targetScope['holding_id'] ?? 0) > 0) {
            return ['scope_type' => 'holding', 'scope_holding_id' => (int) $targetScope['holding_id'], 'scope_department_id' => null, 'scope_division_id' => null];
        }

        return ['scope_type' => 'global', 'scope_holding_id' => null, 'scope_department_id' => null, 'scope_division_id' => null];
    }

    private static function upsertModuleOverride(
        int $userId,
        string $moduleCode,
        string $effect,
        string $accessLevel,
        array $scope,
        int $isActive,
        string $reason,
        int $actorId,
        $now
    ): void {
        $moduleCode = trim($moduleCode);
        if ($moduleCode === '') {
            return;
        }

        $effect = in_array($effect, ['allow', 'deny'], true) ? $effect : 'deny';
        $access = ($effect === 'allow' && $accessLevel === 'full') ? 'full' : 'view';

        $scope = self::normalizeScope($scope);
        $targetId = self::scopeTargetId($scope);

        $q = DB::table('auth_user_module_overrides')
            ->where('auth_user_id', $userId)
            ->where('module_code', $moduleCode)
            ->where('scope_type', $scope['scope_type'])
            ->where('scope_target_id', $targetId);

        $existing = $q->lockForUpdate()->first(['id', 'created_by']);

        $data = [
            'auth_user_id' => $userId,
            'module_code' => $moduleCode,
            'effect' => $effect,
            'access_level' => $access,
            'scope_type' => $scope['scope_type'],
            'scope_holding_id' => $scope['scope_holding_id'],
            'scope_department_id' => $scope['scope_department_id'],
            'scope_division_id' => $scope['scope_division_id'],
            'reason' => trim($reason) !== '' ? trim($reason) : null,
            'is_active' => (int) $isActive,
            'updated_at' => $now,
        ];

        if ($existing) {
            // ✅ jangan overwrite created_by yang lama
            DB::table('auth_user_module_overrides')->where('id', (int) $existing->id)->update($data);
        } else {
            $data['created_at'] = $now;
            $data['created_by'] = $actorId > 0 ? $actorId : null;
            DB::table('auth_user_module_overrides')->insert($data);
        }
    }

    private static function upsertPermissionOverride(
        int $userId,
        string $permissionCode,
        string $effect,
        array $scope,
        int $isActive,
        string $reason,
        int $actorId,
        $now
    ): void {
        $permissionCode = trim($permissionCode);
        if ($permissionCode === '') {
            return;
        }

        $effect = in_array($effect, ['allow', 'deny'], true) ? $effect : 'deny';

        $scope = self::normalizeScope($scope);
        $targetId = self::scopeTargetId($scope);

        $q = DB::table('auth_user_permission_overrides')
            ->where('auth_user_id', $userId)
            ->where('permission_code', $permissionCode)
            ->where('scope_type', $scope['scope_type'])
            ->where('scope_target_id', $targetId);

        $existing = $q->lockForUpdate()->first(['id', 'created_by']);

        $data = [
            'auth_user_id' => $userId,
            'permission_code' => $permissionCode,
            'effect' => $effect,
            'scope_type' => $scope['scope_type'],
            'scope_holding_id' => $scope['scope_holding_id'],
            'scope_department_id' => $scope['scope_department_id'],
            'scope_division_id' => $scope['scope_division_id'],
            'reason' => trim($reason) !== '' ? trim($reason) : null,
            'is_active' => (int) $isActive,
            'updated_at' => $now,
        ];

        if ($existing) {
            // ✅ jangan overwrite created_by yang lama
            DB::table('auth_user_permission_overrides')->where('id', (int) $existing->id)->update($data);
        } else {
            $data['created_at'] = $now;
            $data['created_by'] = $actorId > 0 ? $actorId : null;
            DB::table('auth_user_permission_overrides')->insert($data);
        }
    }
}
