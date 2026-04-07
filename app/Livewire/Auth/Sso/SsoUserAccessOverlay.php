<?php

namespace App\Livewire\Auth\Sso;

use App\Models\Auth\AuthUser;
use App\Services\Auth\AccessCloner;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoUserAccessOverlay extends Component
{
    public int $userId;

    /** UI */
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /** Actor caps */
    public bool $canRoleAssign = false;

    public bool $canOverrideWrite = false;

    /** Target info */
    public array $target = [];

    /** Roles */
    public array $allRoles = [];         // [{id,code,name}]

    public array $selectedRoleIds = [];  // [id,id]

    /** Override options */
    public array $moduleOptions = [];     // ['01005' => '01005 - Inventaris']

    public array $permissionOptions = []; // ['INV_DELETE' => 'INV_DELETE (01005) - desc']

    public array $holdingOptions = [];

    public array $departmentOptions = [];

    public array $divisionOptions = [];

    /** Overrides list */
    public array $moduleOverrides = [];

    public array $permissionOverrides = [];

    /** Override form */
    public string $ovType = 'module'; // module|permission

    public ?int $ovId = null;

    public string $ovModuleCode = '';

    public string $ovPermissionCode = '';

    public string $ovEffect = 'allow';      // allow|deny

    public string $ovAccessLevel = 'view';  // view|full (only for module allow)

    public string $ovScopeType = 'global'; // global|holding|department|division

    public ?int $ovScopeHoldingId = null;

    public ?int $ovScopeDepartmentId = null;

    public ?int $ovScopeDivisionId = null;

    public string $ovReason = '';

    public int $ovIsActive = 1;

    // ===== CLONE ACCESS UI =====
    public bool $canCloneAccess = false;

    public array $cloneUserOptions = []; // [id => "username (email)"]

    public string $cloneMode = 'replace'; // replace|merge

    public ?int $cloneFromUserId = null;

    public bool $cloneRoles = true;

    public bool $cloneModuleOverrides = true;

    public bool $clonePermissionOverrides = true;

    public bool $cloneOnlyActiveOverrides = true;

    public function mount(int $userId): void
    {
        $this->userId = (int) $userId;

        $actor = auth()->user();
        abort_unless($actor, 401);

        $this->canRoleAssign = (bool) ($actor->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false);

        // ✅ override edit: (super admin) OR (role assign) OR (user update)
        $this->canOverrideWrite = (bool) (
            ($actor->isSuperAdmin() ?? false)
            || ($actor->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false)
            || ($actor->hasPermission('SSO_USER_UPDATE') ?? false)
        );

        $this->canCloneAccess = (bool) (
            ($actor->isSuperAdmin() ?? false)
            || ($actor->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false)
            || ($actor->hasPermission('SSO_USER_UPDATE') ?? false)
        );

        $this->loadTarget();
        $this->loadRoles();
        $this->loadOverrideOptions();
        $this->loadOverrides();
        $this->loadCloneUserOptions();
        $this->prefillOverrideScopeFromTarget();
    }

    protected function loadTarget(): void
    {
        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->leftJoin('holdings as h', 'h.id', '=', 'i.holding_id')
            ->leftJoin('departments as d', 'd.id', '=', 'i.department_id')
            ->leftJoin('divisions as dv', 'dv.id', '=', 'i.division_id')
            ->where('u.id', $this->userId)
            ->first([
                'u.id', 'u.username', 'u.email', 'u.is_super_admin', 'u.is_super_scope', 'u.is_locked',
                'i.identity_type', 'i.identity_key', 'i.is_active', 'i.holding_id', 'i.department_id', 'i.division_id',
                DB::raw("COALESCE(h.name,'-') as holding_name"),
                DB::raw("COALESCE(h.alias,'-') as holding_alias"),
                DB::raw("COALESCE(d.name,'-') as department_name"),
                DB::raw("COALESCE(dv.name,'-') as division_name"),
            ]);

        abort_unless($row, 404, 'User tidak ditemukan.');

        $this->target = [
            'id' => (int) $row->id,
            'username' => (string) $row->username,
            'email' => (string) ($row->email ?? ''),
            'is_super_admin' => (int) ($row->is_super_admin ?? 0),
            'is_locked' => (int) ($row->is_locked ?? 0),

            'identity_type' => (string) ($row->identity_type ?? ''),
            'identity_key' => (string) ($row->identity_key ?? ''),
            'identity_active' => (int) ($row->is_active ?? 0),

            'holding_id' => (int) ($row->holding_id ?? 0),
            'department_id' => (int) ($row->department_id ?? 0),
            'division_id' => (int) ($row->division_id ?? 0),

            'holding_name' => (string) ($row->holding_name ?? '-'),
            'holding_alias' => (string) ($row->holding_alias ?? '-'),
            'department_name' => (string) ($row->department_name ?? '-'),
            'division_name' => (string) ($row->division_name ?? '-'),
        ];
    }

    protected function loadRoles(): void
    {
        $this->allRoles = DB::table('auth_roles')
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'code' => (string) $r->code,
                'name' => (string) $r->name,
            ])
            ->toArray();

        $this->selectedRoleIds = DB::table('auth_user_roles')
            ->where('auth_user_id', $this->userId)
            ->pluck('role_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();
    }

    protected function loadOverrideOptions(): void
    {
        $this->moduleOptions = DB::table('auth_modules')
            ->orderBy('code')
            ->get(['code', 'name'])
            ->mapWithKeys(fn ($m) => [(string) $m->code => ((string) $m->code.' - '.(string) $m->name)])
            ->toArray();

        $this->permissionOptions = DB::table('auth_permissions as p')
            ->join('auth_modules as m', function ($j) {
                $j->on('m.code', '=', 'p.module_code')
                    ->where('m.is_active', '=', 1);
            })
            ->where('p.is_active', 1)
            ->orderBy('p.module_code')
            ->orderBy('p.code')
            ->get(['p.code', 'p.module_code', 'p.description'])
            ->mapWithKeys(function ($p) {
                $label = (string) $p->code.' ('.(string) $p->module_code.')';
                if (! empty($p->description)) {
                    $label .= ' - '.(string) $p->description;
                }

                return [(string) $p->code => $label];
            })
            ->toArray();

        $this->holdingOptions = DB::table('holdings')
            ->orderBy('id')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(int) $h->id => ((string) $h->alias.' - '.(string) $h->name)])
            ->toArray();

        // departments/divisions exist in your DB (FK from identities)
        $this->departmentOptions = DB::table('departments')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(int) $d->id => (string) $d->name])
            ->toArray();

        $this->divisionOptions = DB::table('divisions')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($dv) => [(int) $dv->id => (string) $dv->name])
            ->toArray();
    }

    protected function loadCloneUserOptions(): void
    {
        // source list untuk template (exclude target sendiri)
        $this->cloneUserOptions = DB::table('auth_users')
            ->where('id', '<>', $this->userId)
            ->orderBy('username')
            ->get(['id', 'username', 'email'])
            ->mapWithKeys(function ($u) {
                $label = (string) $u->username;
                if (! empty($u->email)) {
                    $label .= ' ('.$u->email.')';
                }

                return [(int) $u->id => $label];
            })
            ->toArray();
    }

    public function cloneAccessFromUser(): void
    {
        $actor = auth()->user();
        abort_unless($actor, 401);

        if (! $this->canCloneAccess) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin clone access.'];

            return;
        }

        $src = (int) ($this->cloneFromUserId ?? 0);
        if ($src <= 0 || ! isset($this->cloneUserOptions[$src])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih source user (template) terlebih dahulu.'];

            return;
        }

        // safety: kalau target super admin, hanya super admin boleh
        if ((int) ($this->target['is_super_admin'] ?? 0) === 1 && ! ($actor->isSuperAdmin() ?? false)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak boleh mengubah akses super admin.'];

            return;
        }

        $result = AccessCloner::cloneOne($src, $this->userId, [
            'mode' => $this->cloneMode,
            'clone_roles' => (bool) $this->cloneRoles,
            'clone_module_overrides' => (bool) $this->cloneModuleOverrides,
            'clone_permission_overrides' => (bool) $this->clonePermissionOverrides,
            'only_active_overrides' => (bool) $this->cloneOnlyActiveOverrides,
            'actor_user_id' => (int) auth()->id(),
        ]);

        // audit (pakai audit() yang sudah kamu punya)
        $this->audit('SSO_ACCESS_CLONE', [
            'target_user_id' => $this->userId,
            'source_user_id' => $src,
            'mode' => $this->cloneMode,
            'clone_roles' => (int) $this->cloneRoles,
            'clone_module_overrides' => (int) $this->cloneModuleOverrides,
            'clone_permission_overrides' => (int) $this->clonePermissionOverrides,
            'only_active_overrides' => (int) $this->cloneOnlyActiveOverrides,
            'result' => $result,
        ]);

        // reload UI overlay
        $this->loadRoles();
        $this->loadOverrides();
        $this->syncProvisioningUnlockIfEligible();

        $this->toast = [
            'show' => true,
            'type' => ($result['ok'] ?? false) ? 'success' : 'warning',
            'message' => ($result['ok'] ?? false)
                ? "Clone berhasil dari {$this->cloneUserOptions[$src]} ({$this->cloneMode})."
                : ($result['message'] ?? 'Clone gagal.'),
        ];
    }

    protected function loadOverrides(): void
    {
        $this->moduleOverrides = DB::table('auth_user_module_overrides')
            ->where('auth_user_id', $this->userId)
            ->orderBy('is_active', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'module_code' => (string) $r->module_code,
                'effect' => (string) $r->effect,
                'access_level' => (string) $r->access_level,
                'scope_type' => (string) $r->scope_type,
                'scope_holding_id' => $r->scope_holding_id !== null ? (int) $r->scope_holding_id : null,
                'scope_department_id' => $r->scope_department_id !== null ? (int) $r->scope_department_id : null,
                'scope_division_id' => $r->scope_division_id !== null ? (int) $r->scope_division_id : null,
                'reason' => (string) ($r->reason ?? ''),
                'is_active' => (int) $r->is_active,
                'created_by' => $r->created_by !== null ? (int) $r->created_by : null,
                'created_at' => (string) ($r->created_at ?? ''),
            ])
            ->toArray();

        $this->permissionOverrides = DB::table('auth_user_permission_overrides')
            ->where('auth_user_id', $this->userId)
            ->orderBy('is_active', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'permission_code' => (string) $r->permission_code,
                'effect' => (string) $r->effect,
                'scope_type' => (string) $r->scope_type,
                'scope_holding_id' => $r->scope_holding_id !== null ? (int) $r->scope_holding_id : null,
                'scope_department_id' => $r->scope_department_id !== null ? (int) $r->scope_department_id : null,
                'scope_division_id' => $r->scope_division_id !== null ? (int) $r->scope_division_id : null,
                'reason' => (string) ($r->reason ?? ''),
                'is_active' => (int) $r->is_active,
                'created_by' => $r->created_by !== null ? (int) $r->created_by : null,
                'created_at' => (string) ($r->created_at ?? ''),
            ])
            ->toArray();
    }

    protected function prefillOverrideScopeFromTarget(): void
    {
        // default form scope: sedekat mungkin ke identity scope (boleh kamu ubah manual)
        $div = (int) ($this->target['division_id'] ?? 0);
        $dep = (int) ($this->target['department_id'] ?? 0);
        $hol = (int) ($this->target['holding_id'] ?? 0);

        if ($div > 0) {
            $this->ovScopeType = 'division';
            $this->ovScopeDivisionId = $div;
            $this->ovScopeDepartmentId = null;
            $this->ovScopeHoldingId = null;

            return;
        }

        if ($dep > 0) {
            $this->ovScopeType = 'department';
            $this->ovScopeDepartmentId = $dep;
            $this->ovScopeDivisionId = null;
            $this->ovScopeHoldingId = null;

            return;
        }

        if ($hol > 0) {
            $this->ovScopeType = 'holding';
            $this->ovScopeHoldingId = $hol;
            $this->ovScopeDepartmentId = null;
            $this->ovScopeDivisionId = null;

            return;
        }

        $this->ovScopeType = 'global';
        $this->ovScopeHoldingId = null;
        $this->ovScopeDepartmentId = null;
        $this->ovScopeDivisionId = null;
    }

    protected function scopePayload(): array
    {
        $type = $this->ovScopeType;

        $payload = [
            'scope_type' => $type,
            'scope_holding_id' => null,
            'scope_department_id' => null,
            'scope_division_id' => null,
        ];

        if ($type === 'holding') {
            $payload['scope_holding_id'] = $this->ovScopeHoldingId ? (int) $this->ovScopeHoldingId : null;
        } elseif ($type === 'department') {
            $payload['scope_department_id'] = $this->ovScopeDepartmentId ? (int) $this->ovScopeDepartmentId : null;
        } elseif ($type === 'division') {
            $payload['scope_division_id'] = $this->ovScopeDivisionId ? (int) $this->ovScopeDivisionId : null;
        }

        return $payload;
    }

    protected function validateScope(): bool
    {
        if ($this->ovScopeType === 'global') {
            return true;
        }

        if ($this->ovScopeType === 'holding' && (int) ($this->ovScopeHoldingId ?? 0) > 0) {
            return true;
        }
        if ($this->ovScopeType === 'department' && (int) ($this->ovScopeDepartmentId ?? 0) > 0) {
            return true;
        }
        if ($this->ovScopeType === 'division' && (int) ($this->ovScopeDivisionId ?? 0) > 0) {
            return true;
        }

        $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Scope ID wajib dipilih sesuai scope_type.'];

        return false;
    }

    protected function clearTargetCache(): void
    {
        if ($u = AuthUser::find($this->userId)) {
            $u->clearAuthCache();
        }
    }

    protected function audit(string $action, array $payload): void
    {
        $action = trim((string) $action);

        // Optional: mapping nama panjang -> pendek (biar konsisten dan enak dibaca)
        $map = [
            'SSO_USER_PERMISSION_OVERRIDE_UPSERT' => 'SSO_PERM_OV_UPSERT',
            'SSO_USER_MODULE_OVERRIDE_UPSERT' => 'SSO_MOD_OV_UPSERT',
            // kalau ada action lain yang panjang, tambahin di sini
        ];

        $finalAction = $map[$action] ?? $action;

        // ✅ karena kolom action sekarang varchar(100)
        $MAX = 100;

        // Safety guard kalau action kepanjangan
        if (mb_strlen($finalAction) > $MAX) {
            $payload['_action_full'] = $finalAction; // simpan versi lengkap
            $finalAction = mb_substr($finalAction, 0, $MAX);
        }

        DB::table('auth_audit_logs')->insert([
            'user_id' => (int) auth()->id(),
            'module_code' => '00000', // pakai string biar konsisten
            'action' => $finalAction,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function resetOverrideForm(): void
    {
        $this->ovId = null;
        $this->ovEffect = 'allow';
        $this->ovAccessLevel = 'view';
        $this->ovModuleCode = '';
        $this->ovPermissionCode = '';
        $this->ovReason = '';
        $this->ovIsActive = 1;

        $this->prefillOverrideScopeFromTarget();
        $this->toast = ['show' => false, 'type' => 'success', 'message' => ''];
    }

    public function editModuleOverride(int $id): void
    {
        $row = DB::table('auth_user_module_overrides')
            ->where('auth_user_id', $this->userId)
            ->where('id', $id)
            ->first();

        if (! $row) {
            return;
        }

        $this->ovType = 'module';
        $this->ovId = (int) $row->id;
        $this->ovModuleCode = (string) $row->module_code;
        $this->ovEffect = (string) $row->effect;
        $this->ovAccessLevel = (string) $row->access_level;

        $this->ovScopeType = (string) $row->scope_type;
        $this->ovScopeHoldingId = $row->scope_holding_id !== null ? (int) $row->scope_holding_id : null;
        $this->ovScopeDepartmentId = $row->scope_department_id !== null ? (int) $row->scope_department_id : null;
        $this->ovScopeDivisionId = $row->scope_division_id !== null ? (int) $row->scope_division_id : null;

        $this->ovReason = (string) ($row->reason ?? '');
        $this->ovIsActive = (int) $row->is_active;
    }

    public function editPermissionOverride(int $id): void
    {
        $row = DB::table('auth_user_permission_overrides')
            ->where('auth_user_id', $this->userId)
            ->where('id', $id)
            ->first();

        if (! $row) {
            return;
        }

        $this->ovType = 'permission';
        $this->ovId = (int) $row->id;
        $this->ovPermissionCode = (string) $row->permission_code;
        $this->ovEffect = (string) $row->effect;

        $this->ovScopeType = (string) $row->scope_type;
        $this->ovScopeHoldingId = $row->scope_holding_id !== null ? (int) $row->scope_holding_id : null;
        $this->ovScopeDepartmentId = $row->scope_department_id !== null ? (int) $row->scope_department_id : null;
        $this->ovScopeDivisionId = $row->scope_division_id !== null ? (int) $row->scope_division_id : null;

        $this->ovReason = (string) ($row->reason ?? '');
        $this->ovIsActive = (int) $row->is_active;
    }

    protected function moduleForPermission(string $permissionCode): ?string
    {
        $mc = DB::table('auth_permissions')->where('code', $permissionCode)->value('module_code');

        return $mc ? (string) $mc : null;
    }

    public function saveOverride(): void
    {
        $actor = auth()->user();
        abort_unless($actor, 401);

        if (! $this->canOverrideWrite) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Read-only (tidak punya izin edit override).'];

            return;
        }

        if (! $this->validateScope()) {
            return;
        }

        $reason = trim($this->ovReason);
        if (mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Reason maksimal 255 karakter.'];

            return;
        }

        $scope = $this->scopePayload();
        $now = now();

        // ✅ scope_target_id (global=0)
        $scopeTargetId = 0;
        if (($scope['scope_type'] ?? '') === 'holding') {
            $scopeTargetId = (int) ($scope['scope_holding_id'] ?? 0);
        } elseif (($scope['scope_type'] ?? '') === 'department') {
            $scopeTargetId = (int) ($scope['scope_department_id'] ?? 0);
        } elseif (($scope['scope_type'] ?? '') === 'division') {
            $scopeTargetId = (int) ($scope['scope_division_id'] ?? 0);
        }

        // ✅ hanya unlock kalau allow + active
        $shouldUnlock = ($this->ovEffect === 'allow' && (int) $this->ovIsActive === 1);

        if ($this->ovType === 'module') {
            $code = trim($this->ovModuleCode);
            if ($code === '' || ! isset($this->moduleOptions[$code])) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Module tidak valid.'];

                return;
            }

            if (! in_array($this->ovEffect, ['allow', 'deny'], true)) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Effect tidak valid.'];

                return;
            }

            $access = ($this->ovEffect === 'allow' && $this->ovAccessLevel === 'full') ? 'full' : 'view';

            $id = null;

            DB::transaction(function () use ($code, $scope, $scopeTargetId, $reason, $access, $now, &$id) {
                // upsert by: user + module + scope_type + scope_target_id
                $q = DB::table('auth_user_module_overrides')
                    ->where('auth_user_id', $this->userId)
                    ->where('module_code', $code)
                    ->where('scope_type', $scope['scope_type'])
                    ->where('scope_target_id', $scopeTargetId);

                $existing = $q->lockForUpdate()->first(['id']);

                $data = [
                    'auth_user_id' => $this->userId,
                    'module_code' => $code,
                    'effect' => $this->ovEffect,
                    'access_level' => $access,
                    'scope_type' => $scope['scope_type'],
                    'scope_holding_id' => $scope['scope_holding_id'],
                    'scope_department_id' => $scope['scope_department_id'],
                    'scope_division_id' => $scope['scope_division_id'],
                    'reason' => $reason !== '' ? $reason : null,
                    'is_active' => (int) $this->ovIsActive,
                    'updated_at' => $now,
                ];

                if ($existing) {
                    DB::table('auth_user_module_overrides')->where('id', (int) $existing->id)->update($data);
                    $id = (int) $existing->id;
                } else {
                    $data['created_at'] = $now;
                    $data['created_by'] = (int) auth()->id();
                    DB::table('auth_user_module_overrides')->insert($data);
                    $id = (int) DB::getPdo()->lastInsertId();
                }

                $this->audit('SSO_USER_MODULE_OVERRIDE_UPSERT', [
                    'target_user_id' => $this->userId,
                    'override_id' => $id,
                    'module_code' => $code,
                    'effect' => $this->ovEffect,
                    'access_level' => $access,
                    'scope' => $scope,
                    'is_active' => (int) $this->ovIsActive,
                    'reason' => $reason,
                ]);
            });

            $this->clearTargetCache();
            $this->loadOverrides();

            if ($shouldUnlock) {
                $this->syncProvisioningUnlockIfEligible(); // ✅ AFTER commit, bukan di dalam TX
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => "Module override tersimpan: {$code} ({$this->ovEffect})."];
            $this->ovId = null;

            return;
        }

        // =========================
        // PERMISSION OVERRIDE
        // =========================
        $perm = trim($this->ovPermissionCode);
        if ($perm === '' || ! isset($this->permissionOptions[$perm])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Permission tidak valid.'];

            return;
        }

        if (! in_array($this->ovEffect, ['allow', 'deny'], true)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Effect tidak valid.'];

            return;
        }

        // ✅ guard: allow permission tapi module belum allowed -> tidak efektif
        $moduleCode = $this->moduleForPermission($perm);
        if (! $moduleCode) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Permission {$perm} tidak ditemukan di auth_permissions."];

            return;
        }

        $u = AuthUser::find($this->userId);
        if (! $u) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'User tidak ditemukan.'];

            return;
        }

        if ($this->ovEffect === 'allow' && ! $u->hasModule($moduleCode)) {
            $this->toast = [
                'show' => true,
                'type' => 'warning',
                'message' => "Tidak bisa ALLOW {$perm} karena module {$moduleCode} belum allowed untuk user. Tambahkan module override ALLOW dulu.",
            ];

            return;
        }

        $id = null;

        DB::transaction(function () use ($perm, $scope, $scopeTargetId, $reason, $now, $moduleCode, &$id) {
            // upsert by: user + permission + scope_type + scope_target_id
            $q = DB::table('auth_user_permission_overrides')
                ->where('auth_user_id', $this->userId)
                ->where('permission_code', $perm)
                ->where('scope_type', $scope['scope_type'])
                ->where('scope_target_id', $scopeTargetId);

            $existing = $q->lockForUpdate()->first(['id']);

            // ✅ jangan set created_by di update
            $data = [
                'auth_user_id' => $this->userId,
                'permission_code' => $perm,
                'effect' => $this->ovEffect,
                'scope_type' => $scope['scope_type'],
                'scope_holding_id' => $scope['scope_holding_id'],
                'scope_department_id' => $scope['scope_department_id'],
                'scope_division_id' => $scope['scope_division_id'],
                'reason' => $reason !== '' ? $reason : null,
                'is_active' => (int) $this->ovIsActive,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('auth_user_permission_overrides')->where('id', (int) $existing->id)->update($data);
                $id = (int) $existing->id;
            } else {
                $data['created_at'] = $now;
                $data['created_by'] = (int) auth()->id();
                DB::table('auth_user_permission_overrides')->insert($data);
                $id = (int) DB::getPdo()->lastInsertId();
            }

            $this->audit('SSO_USER_PERMISSION_OVERRIDE_UPSERT', [
                'target_user_id' => $this->userId,
                'override_id' => $id,
                'permission_code' => $perm,
                'module_code' => $moduleCode,
                'effect' => $this->ovEffect,
                'scope' => $scope,
                'is_active' => (int) $this->ovIsActive,
                'reason' => $reason,
            ]);
        });

        $this->clearTargetCache();
        $this->loadOverrides();

        if ($shouldUnlock) {
            $this->syncProvisioningUnlockIfEligible(); // ✅ AFTER commit, bukan di dalam TX
        }

        $this->toast = ['show' => true, 'type' => 'success', 'message' => "Permission override tersimpan: {$perm} ({$this->ovEffect})."];
        $this->ovId = null;
    }

    public function toggleOverrideActive(string $type, int $id): void
    {
        if (! $this->canOverrideWrite) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Read-only.'];

            return;
        }

        $table = $type === 'module' ? 'auth_user_module_overrides' : 'auth_user_permission_overrides';

        $row = DB::table($table)
            ->where('auth_user_id', $this->userId)
            ->where('id', $id)
            ->first(['id', 'is_active']);

        if (! $row) {
            return;
        }

        $new = ((int) $row->is_active === 1) ? 0 : 1;

        DB::table($table)->where('id', $id)->update(['is_active' => $new, 'updated_at' => now()]);

        $this->audit('SSO_USER_OVERRIDE_TOGGLE', [
            'target_user_id' => $this->userId,
            'type' => $type,
            'override_id' => $id,
            'is_active' => $new,
        ]);

        $this->clearTargetCache();
        $this->loadOverrides();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Override diperbarui (aktif/nonaktif).'];
    }

    public function deleteOverride(string $type, int $id): void
    {
        if (! $this->canOverrideWrite) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Read-only.'];

            return;
        }

        $table = $type === 'module' ? 'auth_user_module_overrides' : 'auth_user_permission_overrides';

        $row = DB::table($table)
            ->where('auth_user_id', $this->userId)
            ->where('id', $id)
            ->first();

        if (! $row) {
            return;
        }

        DB::table($table)->where('id', $id)->delete();

        $this->audit('SSO_USER_OVERRIDE_DELETE', [
            'target_user_id' => $this->userId,
            'type' => $type,
            'override_id' => $id,
        ]);

        $this->clearTargetCache();
        $this->loadOverrides();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Override dihapus.'];
    }

    /** =========================
     *  Effective Modules ✅ (menghormati override)
     *  ========================= */
    public function effectiveModules(): array
    {
        $u = AuthUser::find($this->userId);
        if (! $u) {
            return [];
        }

        if ($u->isSuperAdmin()) {
            return DB::table('auth_modules')
                ->where('is_active', 1)
                ->orderBy('code')
                ->get(['code', 'name', 'route'])
                ->map(fn ($m) => [
                    'module_code' => (string) $m->code,
                    'module_name' => (string) $m->name,
                    'route' => (string) ($m->route ?? ''),
                    'access_level' => 'full',
                    'scope_types' => 'global',
                ])
                ->toArray();
        }

        $map = $u->modulesWithAccessLevel();
        $codes = array_keys($map);
        if (empty($codes)) {
            return [];
        }

        $rows = DB::table('auth_modules')
            ->where('is_active', 1)
            ->whereIn('code', $codes)
            ->orderBy('code')
            ->get(['code', 'name', 'route']);

        $out = [];
        foreach ($rows as $m) {
            $code = (string) $m->code;
            $out[] = [
                'module_code' => $code,
                'module_name' => (string) $m->name,
                'route' => (string) ($m->route ?? ''),
                'access_level' => (string) ($map[$code]['access_level'] ?? 'view'),
                'scope_types' => '-',
            ];
        }

        return $out;
    }

    /** =========================
     *  Effective Permissions ✅ (module-gated)
     *  ========================= */
    public function effectivePermissionsGrouped(): array
    {
        $u = AuthUser::find($this->userId);
        if (! $u) {
            return [];
        }

        // Super admin => semua permission aktif (optional: tetap filter module aktif)
        if ($u->isSuperAdmin()) {
            $rows = DB::table('auth_permissions as p')
                ->leftJoin('auth_modules as m', function ($j) {
                    $j->on('m.code', '=', 'p.module_code')
                        ->where('m.is_active', '=', 1);
                })
                ->where('p.is_active', 1)
                ->orderBy('p.module_code')
                ->orderBy('p.code')
                ->get([
                    'p.module_code',
                    DB::raw('COALESCE(m.name, p.module_code) as module_name'),
                    'p.code',
                    'p.requires_approval',
                    'p.description',
                ]);

            return $this->groupPermRows($rows);
        }

        // ✅ MODULE GATE paling depan
        $allowedModules = array_keys($u->modulesWithAccessLevel());
        if (empty($allowedModules)) {
            return [];
        }

        // ✅ Effective permissions HARUS include override allow/deny
        $permCodes = $u->permissions();
        if (empty($permCodes)) {
            return [];
        }

        // ✅ Ambil metadata permission dari table auth_permissions, tapi hanya untuk module yang allowed
        $rows = DB::table('auth_permissions as p')
            ->leftJoin('auth_modules as m', function ($j) {
                $j->on('m.code', '=', 'p.module_code')
                    ->where('m.is_active', '=', 1);
            })
            ->where('p.is_active', 1)
            ->whereIn('p.module_code', $allowedModules) // ✅ gate keras
            ->whereIn('p.code', $permCodes)             // ✅ hasil akhir (role + override)
            ->orderBy('p.module_code')
            ->orderBy('p.code')
            ->get([
                'p.module_code',
                DB::raw('COALESCE(m.name, p.module_code) as module_name'),
                'p.code',
                'p.requires_approval',
                'p.description',
            ]);

        return $this->groupPermRows($rows);
    }

    /**
     * Helper kecil untuk grouping hasil query permissions
     */
    protected function groupPermRows($rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $mc = (string) $r->module_code;

            $out[$mc] ??= [
                'module_code' => $mc,
                'module_name' => (string) ($r->module_name ?? $mc),
                'items' => [],
            ];

            $out[$mc]['items'][] = [
                'code' => (string) $r->code,
                'requires_approval' => (int) ($r->requires_approval ?? 0),
                'description' => (string) ($r->description ?? ''),
            ];
        }

        return array_values($out);
    }

    protected function syncProvisioningUnlockIfEligible(): void
    {
        $row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $this->userId)
            ->first(['u.is_super_admin', 'u.is_locked', 'u.last_login_at', 'i.is_active']);

        if (! $row) {
            return;
        }

        if ((int) ($row->is_super_admin ?? 0) === 1) {
            return;
        }
        if ((int) ($row->is_locked ?? 0) !== 1) {
            return;
        }
        if ($row->last_login_at !== null) {
            return;
        }
        if ((int) ($row->is_active ?? 0) !== 1) {
            return;
        }

        $hasRole = DB::table('auth_user_roles')->where('auth_user_id', $this->userId)->exists();

        $hasAllowModOv = DB::table('auth_user_module_overrides')
            ->where('auth_user_id', $this->userId)
            ->where('is_active', 1)
            ->where('effect', 'allow')
            ->exists();

        $hasAllowPermOv = DB::table('auth_user_permission_overrides')
            ->where('auth_user_id', $this->userId)
            ->where('is_active', 1)
            ->where('effect', 'allow')
            ->exists();

        if (! ($hasRole || $hasAllowModOv || $hasAllowPermOv)) {
            return;
        }

        DB::table('auth_users')->where('id', $this->userId)->update([
            'is_locked' => 0,
            'updated_at' => now(),
        ]);

        // refresh target info biar badge "Locked" berubah di overlay
        $this->loadTarget();
    }

    public function saveRoles(): void
    {
        $actor = auth()->user();
        abort_unless($actor, 401);

        if (! $this->canRoleAssign) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin assign role.'];

            return;
        }

        if ((int) ($this->target['is_super_admin'] ?? 0) === 1 && ! ($actor->isSuperAdmin() ?? false)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak boleh mengubah role super admin.'];

            return;
        }

        $newIds = array_values(array_unique(array_map('intval', $this->selectedRoleIds)));

        DB::transaction(function () use ($newIds) {
            $existing = DB::table('auth_user_roles')
                ->where('auth_user_id', $this->userId)
                ->lockForUpdate()
                ->pluck('role_id')
                ->map(fn ($x) => (int) $x)
                ->toArray();

            $toDelete = array_values(array_diff($existing, $newIds));
            $toInsert = array_values(array_diff($newIds, $existing));

            if (! empty($toDelete)) {
                DB::table('auth_user_roles')
                    ->where('auth_user_id', $this->userId)
                    ->whereIn('role_id', $toDelete)
                    ->delete();
            }

            if (! empty($toInsert)) {
                $now = now();
                $rows = array_map(fn ($rid) => [
                    'auth_user_id' => $this->userId,
                    'role_id' => (int) $rid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $toInsert);

                DB::table('auth_user_roles')->insert($rows);
            }

            if ($u = AuthUser::find($this->userId)) {
                $u->clearAuthCache();
            }

            $this->audit('SSO_USER_ROLES_UPDATE', [
                'target_user_id' => $this->userId,
                'added_role_ids' => $toInsert,
                'removed_role_ids' => $toDelete,
            ]);
        });

        $this->loadRoles();
        $this->loadOverrides();
        $this->syncProvisioningUnlockIfEligible();

        $this->dispatch('sso-user-access-updated',
            userId: $this->userId,
            username: (string) ($this->target['username'] ?? ''),
            email: (string) ($this->target['email'] ?? '')
        );
    }

    public function close(): void
    {
        $this->dispatch('sso-user-overlay-close');
    }

    public function render()
    {
        return view('livewire.auth.sso.sso-user-access-overlay', [
            'effectiveModules' => $this->effectiveModules(),
            'effectivePermissionsGrouped' => $this->effectivePermissionsGrouped(),
        ]);
    }
}
