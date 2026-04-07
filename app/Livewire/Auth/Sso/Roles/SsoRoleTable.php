<?php

namespace App\Livewire\Auth\Sso\Roles;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SsoRoleTable extends Component
{
    use WithPagination;

    /* ===================== UI GLOBAL ===================== */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /* ===================== CAPABILITIES ===================== */
    public bool $canView = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public bool $canRoleModuleUpdate = false;

    public bool $canRolePermissionUpdate = false;

    /* ===================== FILTER & SORT ===================== */
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = ['code', 'name'];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'code'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* ===================== OVERLAY ===================== */
    public ?string $overlayMode = null; // null|'create'|'edit'|'modules'|'permissions'

    public ?int $overlayRoleId = null;

    /* ===================== ROLE FORM ===================== */
    public string $role_code = '';

    public string $role_name = '';

    /* ===================== MODULES FORM ===================== */
    public array $modulesForm = [];  // [module_code => ['enabled'=>bool,'access_level'=>'view|full','scope_type'=>null|holding|department|division,'holding_id'=>?, 'department_id'=>?, 'division_id'=>?]]

    public array $modulesList = [];  // list of modules from DB

    public array $holdingOptions = [];

    public array $departmentOptions = [];

    public array $divisionOptions = [];

    /* ===================== PERMISSIONS FORM ===================== */
    public array $permissionGroups = [];     // [module_code => ['module_name'=>..., 'items'=>[['id'=>..,'code'=>..,'desc'=>..]]]]

    public array $selectedPermissionIds = []; // [permission_id => true]

    public string $permissionSearch = '';

    /* ===================== BOOT ===================== */
    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canView = (bool) ($u?->hasPermission('SSO_ROLE_VIEW') ?? false);
        $this->canCreate = (bool) ($u?->hasPermission('SSO_ROLE_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('SSO_ROLE_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('SSO_ROLE_DELETE') ?? false);
        $this->canRoleModuleUpdate = (bool) ($u?->hasPermission('SSO_ROLE_MODULE_UPDATE') ?? false);
        $this->canRolePermissionUpdate = (bool) ($u?->hasPermission('SSO_ROLE_PERMISSION_UPDATE') ?? false);
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'SSO Governance', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'Roles', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();
        abort_unless($this->canView, 403, 'Forbidden');

        $this->primeOptions();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    private function primeOptions(): void
    {
        $this->modulesList = DB::table('auth_modules')
            ->where('is_active', 1)
            ->orderBy('code', 'asc')
            ->get(['code', 'name', 'route'])
            ->map(fn ($m) => [
                'code' => (string) $m->code,
                'name' => (string) $m->name,
                'route' => (string) ($m->route ?? ''),
            ])
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
            ->mapWithKeys(fn ($dv) => [(string) $dv->id => (string) $dv->name])
            ->toArray();
    }

    /* ===================== QUERY ===================== */
    protected function rolesQuery()
    {
        $sf = in_array($this->sortField, $this->allowedSortFields, true) ? $this->sortField : 'code';
        $sd = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return DB::table('auth_roles as r')
            ->select([
                'r.id', 'r.code', 'r.name',
                DB::raw('(SELECT COUNT(*) FROM auth_user_roles ur WHERE ur.auth_user_id IS NOT NULL AND ur.role_id = r.id) as users_count'),
                DB::raw('(SELECT COUNT(*) FROM auth_role_modules rm WHERE rm.role_id = r.id AND rm.is_active=1) as modules_count'),
                DB::raw('(SELECT COUNT(*) FROM auth_role_permissions rp WHERE rp.role_id = r.id) as permissions_count'),
            ])
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($w) use ($s) {
                    $w->where('r.code', 'like', "%{$s}%")
                        ->orWhere('r.name', 'like', "%{$s}%");
                });
            })
            ->orderBy("r.$sf", $sd)
            ->orderBy('r.id', 'desc');
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            $this->resetPage();

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['search', 'perPage', 'sortField', 'sortDirection'], true)) {
            $this->resetPage();
        }
    }

    /* ===================== OVERLAY HELPERS ===================== */
    public function closeOverlay(): void
    {
        $this->reset([
            'overlayMode', 'overlayRoleId',
            'role_code', 'role_name',
            'modulesForm',
            'permissionGroups', 'selectedPermissionIds', 'permissionSearch',
        ]);
    }

    private function roleLabel(?object $roleRow): string
    {
        if (! $roleRow) {
            return 'role';
        }
        $code = (string) ($roleRow->code ?? '');
        $name = (string) ($roleRow->name ?? '');

        return trim($code.' - '.$name);
    }

    /* ===================== ROLE CRUD ===================== */
    public function openCreate(): void
    {
        abort_unless($this->canCreate, 403, 'No permission create role');

        $this->closeOverlay();
        $this->overlayMode = 'create';
        $this->overlayRoleId = null;
        $this->role_code = '';
        $this->role_name = '';
    }

    public function openEdit(int $roleId): void
    {
        abort_unless($this->canUpdate, 403, 'No permission update role');

        $role = DB::table('auth_roles')->where('id', $roleId)->first(['id', 'code', 'name']);
        abort_unless($role, 404, 'Role not found');

        $this->closeOverlay();
        $this->overlayMode = 'edit';
        $this->overlayRoleId = (int) $role->id;
        $this->role_code = (string) $role->code;
        $this->role_name = (string) $role->name;
    }

    public function saveRole(): void
    {
        if ($this->overlayMode !== 'create' && $this->overlayMode !== 'edit') {
            return;
        }

        $code = trim($this->role_code);
        $name = trim($this->role_name);

        if ($code === '' || mb_strlen($code) > 30) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Role code wajib diisi (maks 30).'];

            return;
        }
        if ($name === '' || mb_strlen($name) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Role name wajib diisi (maks 255).'];

            return;
        }

        $exists = DB::table('auth_roles')
            ->where('code', $code)
            ->when($this->overlayMode === 'edit' && $this->overlayRoleId, fn ($q) => $q->where('id', '<>', (int) $this->overlayRoleId))
            ->exists();

        if ($exists) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Role code '{$code}' sudah digunakan."];

            return;
        }

        $now = now();
        $actorId = (int) auth()->id();

        if ($this->overlayMode === 'create') {
            abort_unless($this->canCreate, 403);

            DB::table('auth_roles')->insert([
                'code' => $code,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('auth_audit_logs')->insert([
                'user_id' => $actorId,
                'module_code' => '00000',
                'action' => 'SSO_ROLE_CREATE',
                'payload' => json_encode(['code' => $code, 'name' => $name], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => "Role dibuat: {$code} - {$name}"];
            $this->closeOverlay();
            $this->resetPage();

            return;
        }

        abort_unless($this->canUpdate, 403);

        $rid = (int) $this->overlayRoleId;
        DB::table('auth_roles')->where('id', $rid)->update([
            'code' => $code,
            'name' => $name,
            'updated_at' => $now,
        ]);

        DB::table('auth_audit_logs')->insert([
            'user_id' => $actorId,
            'module_code' => '00000',
            'action' => 'SSO_ROLE_UPDATE',
            'payload' => json_encode(['role_id' => $rid, 'code' => $code, 'name' => $name], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip' => request()->ip(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => "Role diperbarui: {$code} - {$name}"];
        $this->closeOverlay();
        $this->resetPage();
    }

    /* ===================== MODULE ACCESS OVERLAY ===================== */
    public function openModules(int $roleId): void
    {
        abort_unless($this->canRoleModuleUpdate, 403, 'No permission update role modules');

        $role = DB::table('auth_roles')->where('id', $roleId)->first(['id', 'code', 'name']);
        abort_unless($role, 404, 'Role not found');

        $this->closeOverlay();
        $this->overlayMode = 'modules';
        $this->overlayRoleId = (int) $role->id;
        $this->role_name = (string) $role->name;

        $existing = DB::table('auth_role_modules')
            ->where('role_id', (int) $role->id)
            ->get(['module_code', 'scope_type', 'scope_holding_id', 'scope_department_id', 'scope_division_id', 'access_level', 'is_active'])
            ->keyBy('module_code');

        $form = [];
        foreach ($this->modulesList as $m) {
            $code = (string) $m['code'];
            $ex = $existing[$code] ?? null;

            $scopeType = $ex ? $ex->scope_type : null;

            $form[$code] = [
                'enabled' => (bool) ($ex && (int) $ex->is_active === 1),
                'access_level' => $ex ? (string) $ex->access_level : 'view',
                'scope_type' => $scopeType, // null|holding|department|division
                'holding_id' => $ex ? (string) ($ex->scope_holding_id ?? '') : '',
                'department_id' => $ex ? (string) ($ex->scope_department_id ?? '') : '',
                'division_id' => $ex ? (string) ($ex->scope_division_id ?? '') : '',
            ];
        }

        $this->modulesForm = $form;
    }

    public function saveModules(): void
    {
        abort_unless($this->overlayMode === 'modules' && $this->overlayRoleId, 400, 'Invalid state');
        abort_unless($this->canRoleModuleUpdate, 403);

        $rid = (int) $this->overlayRoleId;
        $role = DB::table('auth_roles')->where('id', $rid)->first(['id', 'code', 'name']);
        abort_unless($role, 404);

        $now = now();
        $actorId = (int) auth()->id();

        DB::transaction(function () use ($rid, $role, $now, $actorId) {
            $existing = DB::table('auth_role_modules')
                ->where('role_id', $rid)
                ->lockForUpdate()
                ->get(['module_code'])
                ->keyBy('module_code');

            foreach ($this->modulesList as $m) {
                $code = (string) $m['code'];
                $row = $this->modulesForm[$code] ?? null;

                $enabled = (bool) ($row['enabled'] ?? false);
                $access = (($row['access_level'] ?? 'view') === 'full') ? 'full' : 'view';

                $scopeType = $row['scope_type'] ?? null;
                $scopeType = ($scopeType === '' ? null : $scopeType);

                $holdingId = ($scopeType === 'holding') ? (int) ($row['holding_id'] ?? 0) : null;
                $deptId = ($scopeType === 'department') ? (int) ($row['department_id'] ?? 0) : null;
                $divId = ($scopeType === 'division') ? (int) ($row['division_id'] ?? 0) : null;

                // normalize invalid selections
                if ($scopeType === 'holding' && (! $holdingId || $holdingId <= 0)) {
                    $holdingId = null;
                }
                if ($scopeType === 'department' && (! $deptId || $deptId <= 0)) {
                    $deptId = null;
                }
                if ($scopeType === 'division' && (! $divId || $divId <= 0)) {
                    $divId = null;
                }

                $payload = [
                    'scope_type' => $scopeType,
                    'scope_holding_id' => $holdingId,
                    'scope_department_id' => $deptId,
                    'scope_division_id' => $divId,
                    'access_level' => $access,
                    'is_active' => $enabled ? 1 : 0,
                    'updated_at' => $now,
                ];

                if (isset($existing[$code])) {
                    DB::table('auth_role_modules')
                        ->where('role_id', $rid)
                        ->where('module_code', $code)
                        ->update($payload);
                } else {
                    DB::table('auth_role_modules')->insert(array_merge($payload, [
                        'role_id' => $rid,
                        'module_code' => $code,
                        'created_at' => $now,
                    ]));
                }
            }

            DB::table('auth_audit_logs')->insert([
                'user_id' => $actorId,
                'module_code' => '00000',
                'action' => 'SSO_ROLE_MODULE_UPDATE',
                'payload' => json_encode(['role_id' => $rid, 'role_code' => $role->code], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        $this->clearCacheForRoleUsers($rid);

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Modules access tersimpan untuk '.$this->roleLabel($role),
        ];

        $this->closeOverlay();
        $this->resetPage();
    }

    /* ===================== PERMISSIONS OVERLAY ===================== */
    public function openPermissions(int $roleId): void
    {
        abort_unless($this->canRolePermissionUpdate, 403, 'No permission update role permissions');

        $role = DB::table('auth_roles')->where('id', $roleId)->first(['id', 'code', 'name']);
        abort_unless($role, 404, 'Role not found');

        $this->closeOverlay();
        $this->overlayMode = 'permissions';
        $this->overlayRoleId = (int) $role->id;
        $this->role_name = (string) $role->name;

        // load all permissions grouped by module_code
        $mods = DB::table('auth_modules')->where('is_active', 1)->get(['code', 'name'])->keyBy('code');

        $all = DB::table('auth_permissions')
            ->where('is_active', 1)
            ->orderBy('module_code', 'asc')
            ->orderBy('code', 'asc')
            ->get(['id', 'code', 'module_code', 'description', 'requires_approval']);

        $groups = [];
        foreach ($all as $p) {
            $mc = (string) $p->module_code;
            $groups[$mc] ??= [
                'module_code' => $mc,
                'module_name' => (string) ($mods[$mc]->name ?? $mc),
                'items' => [],
            ];
            $groups[$mc]['items'][] = [
                'id' => (int) $p->id,
                'code' => (string) $p->code,
                'desc' => (string) ($p->description ?? ''),
                'requires_approval' => (int) ($p->requires_approval ?? 0),
            ];
        }

        $this->permissionGroups = $groups;

        // selected permission ids for this role
        $selected = DB::table('auth_role_permissions')
            ->where('role_id', (int) $role->id)
            ->pluck('permission_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        $map = [];
        foreach ($selected as $pid) {
            $map[$pid] = true;
        }
        $this->selectedPermissionIds = $map;
    }

    public function savePermissions(): void
    {
        abort_unless($this->overlayMode === 'permissions' && $this->overlayRoleId, 400, 'Invalid state');
        abort_unless($this->canRolePermissionUpdate, 403);

        $rid = (int) $this->overlayRoleId;
        $role = DB::table('auth_roles')->where('id', $rid)->first(['id', 'code', 'name']);
        abort_unless($role, 404);

        $now = now();
        $actorId = (int) auth()->id();

        $ids = array_keys(array_filter($this->selectedPermissionIds ?? [], fn ($v) => (bool) $v));
        $ids = array_values(array_unique(array_map('intval', $ids)));

        DB::transaction(function () use ($rid, $ids, $now, $actorId, $role) {
            // lock role permissions rows
            DB::table('auth_role_permissions')->where('role_id', $rid)->lockForUpdate()->get();

            DB::table('auth_role_permissions')->where('role_id', $rid)->delete();

            if (! empty($ids)) {
                $rows = [];
                foreach ($ids as $pid) {
                    $rows[] = [
                        'role_id' => $rid,
                        'permission_id' => (int) $pid,
                    ];
                }
                DB::table('auth_role_permissions')->insert($rows);
            }

            DB::table('auth_audit_logs')->insert([
                'user_id' => $actorId,
                'module_code' => '00000',
                'action' => 'SSO_ROLE_PERMISSION_UPDATE',
                'payload' => json_encode(['role_id' => $rid, 'role_code' => $role->code, 'count' => count($ids)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        $this->clearCacheForRoleUsers($rid);

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Permissions tersimpan untuk '.$this->roleLabel($role),
        ];

        $this->closeOverlay();
        $this->resetPage();
    }

    public function filteredPermissionGroups(): array
    {
        $q = trim($this->permissionSearch);
        if ($q === '') {
            return $this->permissionGroups;
        }

        $qLower = mb_strtolower($q, 'UTF-8');
        $out = [];

        foreach ($this->permissionGroups as $mc => $g) {
            $items = array_filter($g['items'], function ($it) use ($qLower) {
                $code = mb_strtolower((string) ($it['code'] ?? ''), 'UTF-8');
                $desc = mb_strtolower((string) ($it['desc'] ?? ''), 'UTF-8');

                return str_contains($code, $qLower) || str_contains($desc, $qLower);
            });

            if (! empty($items)) {
                $out[$mc] = [
                    'module_code' => $g['module_code'],
                    'module_name' => $g['module_name'],
                    'items' => array_values($items),
                ];
            }
        }

        return $out;
    }

    /* ===================== CACHE INVALIDATION ===================== */
    private function clearCacheForRoleUsers(int $roleId): void
    {
        // ambil user yang punya role ini
        $ids = DB::table('auth_user_roles')
            ->where('role_id', $roleId)
            ->pluck('auth_user_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        if (empty($ids)) {
            return;
        }

        // chunk-safe
        $chunks = array_chunk($ids, 200);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $uid) {
                Cache::forget("auth:user:{$uid}:permissions");
                Cache::forget("auth:user:{$uid}:modules");
            }
        }
    }

    public function render()
    {
        $rows = $this->rolesQuery()->paginate($this->perPage);

        return view('livewire.auth.sso.roles.sso-role-table', [
            'rows' => $rows,
            // (opsional) biar eksplisit jika suatu saat view di-include
            'canCreate' => $this->canCreate,
            'canUpdate' => $this->canUpdate,
            'canRoleModuleUpdate' => $this->canRoleModuleUpdate,
            'canRolePermissionUpdate' => $this->canRolePermissionUpdate,
        ])->layout('components.sccr-layout');
    }
}
