<?php

namespace App\Livewire\Auth\Sso\Roles;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoRoleEditorOverlay extends Component
{
    public int $roleId;

    public array $role = []; // id, code, name

    public bool $canModuleUpdate = false;

    public bool $canPermissionUpdate = false;

    public string $tab = 'modules'; // modules|permissions

    /** MODULE EDITOR */
    public array $allModules = []; // code=>label

    public string $addModuleCode = '';

    public array $moduleRows = []; // list rows

    /** scope option lists */
    public array $holdingOptions = [];

    public array $departmentOptions = [];

    public array $divisionOptions = [];

    /** PERMISSION EDITOR */
    public array $permissionsGrouped = []; // grouped by module_code

    public array $selectedPermissionIds = []; // ids

    public function mount(int $roleId): void
    {
        $this->roleId = (int) $roleId;

        $actor = auth()->user();
        abort_unless($actor, 401);

        $this->canModuleUpdate = (bool) ($actor->hasPermission('SSO_ROLE_MODULE_UPDATE') ?? false);
        $this->canPermissionUpdate = (bool) ($actor->hasPermission('SSO_ROLE_PERMISSION_UPDATE') ?? false);

        $r = DB::table('auth_roles')->where('id', $this->roleId)->first(['id', 'code', 'name']);
        abort_unless($r, 404, 'Role tidak ditemukan.');
        $this->role = ['id' => (int) $r->id, 'code' => (string) $r->code, 'name' => (string) $r->name];

        $this->loadOptions();
        $this->loadModulesEditor();
        $this->loadPermissionsEditor();
    }

    protected function loadOptions(): void
    {
        $this->allModules = DB::table('auth_modules')
            ->where('is_active', 1)
            ->orderBy('code')
            ->get(['code', 'name'])
            ->mapWithKeys(fn ($m) => [(string) $m->code => ((string) $m->code.' - '.(string) $m->name)])
            ->toArray();

        $this->holdingOptions = DB::table('holdings')
            ->orderBy('name')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(string) $h->id => ($h->name.($h->alias ? ' - '.$h->alias : ''))])
            ->toArray();

        $this->departmentOptions = DB::table('departments')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => (string) $d->name])
            ->toArray();

        $this->divisionOptions = DB::table('divisions')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => (string) $d->name])
            ->toArray();
    }

    protected function loadModulesEditor(): void
    {
        $rows = DB::table('auth_role_modules')
            ->where('role_id', $this->roleId)
            ->orderBy('module_code')
            ->get();

        $out = [];
        foreach ($rows as $rm) {
            $out[] = [
                'module_code' => (string) $rm->module_code,
                'scope_type' => (string) ($rm->scope_type ?? ''), // '' => global
                'scope_holding_id' => $rm->scope_holding_id ? (string) $rm->scope_holding_id : '',
                'scope_department_id' => $rm->scope_department_id ? (string) $rm->scope_department_id : '',
                'scope_division_id' => $rm->scope_division_id ? (string) $rm->scope_division_id : '',
                'access_level' => (string) ($rm->access_level ?? 'view'),
                'is_active' => (int) ($rm->is_active ?? 1),
            ];
        }

        $this->moduleRows = $out;
    }

    protected function loadPermissionsEditor(): void
    {
        $this->selectedPermissionIds = DB::table('auth_role_permissions')
            ->where('role_id', $this->roleId)
            ->pluck('permission_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        $rows = DB::table('auth_permissions as p')
            ->leftJoin('auth_modules as m', 'm.code', '=', 'p.module_code')
            ->where('p.is_active', 1)
            ->orderBy('p.module_code')
            ->orderBy('p.code')
            ->get([
                'p.id', 'p.code', 'p.module_code', 'p.requires_approval', 'p.description',
                DB::raw('COALESCE(m.name, p.module_code) as module_name'),
            ]);

        $grp = [];
        foreach ($rows as $r) {
            $mc = (string) $r->module_code;
            $grp[$mc] ??= ['module_code' => $mc, 'module_name' => (string) $r->module_name, 'items' => []];
            $grp[$mc]['items'][] = [
                'id' => (int) $r->id,
                'code' => (string) $r->code,
                'requires_approval' => (int) $r->requires_approval,
                'description' => (string) ($r->description ?? ''),
            ];
        }

        $this->permissionsGrouped = array_values($grp);
    }

    public function addModuleRow(): void
    {
        if (! $this->canModuleUpdate) {
            return;
        }

        $code = trim($this->addModuleCode);
        if ($code === '' || ! isset($this->allModules[$code])) {
            return;
        }

        // prevent duplicate same module+scope row (basic)
        foreach ($this->moduleRows as $r) {
            if (($r['module_code'] ?? '') === $code && ($r['scope_type'] ?? '') === '') {
                return;
            }
        }

        $this->moduleRows[] = [
            'module_code' => $code,
            'scope_type' => '',
            'scope_holding_id' => '',
            'scope_department_id' => '',
            'scope_division_id' => '',
            'access_level' => 'view',
            'is_active' => 1,
        ];

        $this->addModuleCode = '';
    }

    public function removeModuleRow(int $idx): void
    {
        if (! $this->canModuleUpdate) {
            return;
        }
        if (! isset($this->moduleRows[$idx])) {
            return;
        }

        array_splice($this->moduleRows, $idx, 1);
    }

    protected function sanitizeModuleRows(): array
    {
        $out = [];
        foreach ($this->moduleRows as $r) {
            $mc = trim((string) ($r['module_code'] ?? ''));
            if ($mc === '') {
                continue;
            }

            $scope = (string) ($r['scope_type'] ?? '');
            if (! in_array($scope, ['', 'holding', 'department', 'division'], true)) {
                $scope = '';
            }

            $h = (string) ($r['scope_holding_id'] ?? '');
            $d = (string) ($r['scope_department_id'] ?? '');
            $v = (string) ($r['scope_division_id'] ?? '');

            if ($scope === '') {
                $h = '';
                $d = '';
                $v = '';
            }
            if ($scope === 'holding') {
                $d = '';
                $v = '';
            }
            if ($scope === 'department') {
                $h = '';
                $v = '';
            }
            if ($scope === 'division') {
                $h = '';
                $d = '';
            }

            // required check per scope
            if ($scope === 'holding' && $h === '') {
                continue;
            }
            if ($scope === 'department' && $d === '') {
                continue;
            }
            if ($scope === 'division' && $v === '') {
                continue;
            }

            $al = (string) ($r['access_level'] ?? 'view');
            if (! in_array($al, ['view', 'full'], true)) {
                $al = 'view';
            }

            $active = ((int) ($r['is_active'] ?? 1) === 1) ? 1 : 0;

            $out[] = [
                'role_id' => $this->roleId,
                'module_code' => $mc,
                'scope_type' => ($scope === '' ? null : $scope),
                'scope_holding_id' => ($h === '' ? null : (int) $h),
                'scope_department_id' => ($d === '' ? null : (int) $d),
                'scope_division_id' => ($v === '' ? null : (int) $v),
                'access_level' => $al,
                'is_active' => $active,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // de-dup identical rows
        $seen = [];
        $uniq = [];
        foreach ($out as $row) {
            $k = implode('|', [
                $row['module_code'],
                (string) ($row['scope_type'] ?? ''),
                (string) ($row['scope_holding_id'] ?? ''),
                (string) ($row['scope_department_id'] ?? ''),
                (string) ($row['scope_division_id'] ?? ''),
            ]);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $uniq[] = $row;
        }

        return $uniq;
    }

    protected function clearUsersCacheForRole(): void
    {
        $userIds = DB::table('auth_user_roles')
            ->where('role_id', $this->roleId)
            ->pluck('auth_user_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        foreach ($userIds as $uid) {
            Cache::forget("auth:user:{$uid}:permissions");
            Cache::forget("auth:user:{$uid}:modules");
        }
    }

    public function saveModules(): void
    {
        if (! $this->canModuleUpdate) {
            return;
        }

        $rows = $this->sanitizeModuleRows();

        DB::transaction(function () use ($rows) {
            DB::table('auth_role_modules')->where('role_id', $this->roleId)->delete();
            if (! empty($rows)) {
                DB::table('auth_role_modules')->insert($rows);
            }

            $this->clearUsersCacheForRole();

            DB::table('auth_audit_logs')->insert([
                'user_id' => (int) auth()->id(),
                'module_code' => '00000',
                'action' => 'SSO_ROLE_MODULES_UPDATE',
                'payload' => json_encode([
                    'role_id' => $this->roleId,
                    'role_code' => $this->role['code'] ?? '',
                    'count' => count($rows),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->loadModulesEditor();

        $this->dispatch('sso-role-editor-saved',
            what: 'Modules',
            roleCode: (string) ($this->role['code'] ?? ''),
            roleName: (string) ($this->role['name'] ?? '')
        );
    }

    public function savePermissions(): void
    {
        if (! $this->canPermissionUpdate) {
            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->selectedPermissionIds)));

        DB::transaction(function () use ($ids) {
            DB::table('auth_role_permissions')->where('role_id', $this->roleId)->delete();

            if (! empty($ids)) {
                $rows = array_map(fn ($pid) => ['role_id' => $this->roleId, 'permission_id' => (int) $pid], $ids);
                DB::table('auth_role_permissions')->insert($rows);
            }

            $this->clearUsersCacheForRole();

            DB::table('auth_audit_logs')->insert([
                'user_id' => (int) auth()->id(),
                'module_code' => '00000',
                'action' => 'SSO_ROLE_PERMISSIONS_UPDATE',
                'payload' => json_encode([
                    'role_id' => $this->roleId,
                    'role_code' => $this->role['code'] ?? '',
                    'count' => count($ids),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->loadPermissionsEditor();

        $this->dispatch('sso-role-editor-saved',
            what: 'Permissions',
            roleCode: (string) ($this->role['code'] ?? ''),
            roleName: (string) ($this->role['name'] ?? '')
        );
    }

    public function render()
    {
        return view('livewire.auth.sso.roles.sso-role-editor-overlay', [
            'role' => $this->role,
            'canModuleUpdate' => $this->canModuleUpdate,
            'canPermissionUpdate' => $this->canPermissionUpdate,
        ]);
    }
}
