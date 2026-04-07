<?php

namespace App\Livewire\Auth\Sso\Nav;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;

class SsoNavItemTable extends Component
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

    /* ===================== TREE VIEW ===================== */
    public bool $treeMode = true;

    public array $expanded = []; // [id => true]

    /* ===================== FILTER & SORT ===================== */
    public string $search = '';

    public string $filterModule = '';

    public string $filterActive = ''; // ''|1|0

    // dipakai untuk list mode saja
    public int $perPage = 25;

    public string $sortField = 'sort_order';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = [
        'nav_code', 'label', 'module_code', 'sort_order', 'is_active',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterModule' => ['except' => ''],
        'filterActive' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortField' => ['except' => 'sort_order'],
        'sortDirection' => ['except' => 'asc'],
        'treeMode' => ['except' => true],
    ];

    /* ===================== OPTIONS ===================== */
    public array $moduleOptions = [];       // [code => "code - name"]

    public array $permissionOptions = [];   // [code => "code (module)"]

    public array $permissionModuleMap = []; // [code => module_code]

    public array $parentOptions = [];       // [id => label] (tree-ish)

    /* ===================== MODAL CREATE/EDIT ===================== */
    public bool $showModal = false;

    public string $modalMode = 'create'; // create|edit

    public ?int $editingId = null;

    /* ===================== FORM ===================== */
    public string $nav_code = '';

    public ?int $parent_id = null;

    public string $module_code = '';

    public string $label = '';

    public string $route_name = '';

    public string $permission_code = '';

    public string $icon = '';

    public int $sort_order = 0;

    public int $is_active = 1;

    /* ===================== DELETE CONFIRM ===================== */
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deleteConfirm = '';

    /* ===================== BOOT ===================== */
    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canView = (bool) ($u?->hasPermission('SSO_NAV_VIEW') ?? false);
        $this->canCreate = (bool) ($u?->hasPermission('SSO_NAV_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('SSO_NAV_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('SSO_NAV_DELETE') ?? false);
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'SSO Governance', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'Menu Editor', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();
        abort_unless($this->canView, 403, 'Forbidden');

        $this->primeOptions();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    private function toastSuccess(string $msg): void
    {
        $this->toast = ['show' => true, 'type' => 'success', 'message' => $msg];
    }

    private function toastWarn(string $msg): void
    {
        $this->toast = ['show' => true, 'type' => 'warning', 'message' => $msg];
    }

    /* ===================== OPTIONS ===================== */
    private function primeOptions(): void
    {
        $this->moduleOptions = DB::table('auth_modules')
            ->where('is_active', 1)
            ->orderBy('code', 'asc')
            ->get(['code', 'name'])
            ->mapWithKeys(fn ($m) => [
                (string) $m->code => ((string) $m->code.' - '.(string) $m->name),
            ])
            ->toArray();

        $perms = DB::table('auth_permissions')
            ->where('is_active', 1)
            ->orderBy('module_code', 'asc')
            ->orderBy('code', 'asc')
            ->get(['code', 'module_code']);

        $this->permissionOptions = [];
        $this->permissionModuleMap = [];

        foreach ($perms as $p) {
            $code = (string) $p->code;
            $mc = (string) $p->module_code;
            $this->permissionOptions[$code] = $code.' ('.$mc.')';
            $this->permissionModuleMap[$code] = $mc;
        }

        $this->refreshParentOptions($this->editingId);
    }

    private function refreshParentOptions(?int $excludeId = null): void
    {
        $rows = DB::table('auth_nav_items')
            ->orderByRaw('COALESCE(parent_id, 0) ASC')
            ->orderBy('sort_order', 'asc')
            ->orderBy('nav_code', 'asc')
            ->get(['id', 'nav_code', 'label', 'parent_id'])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'nav_code' => (string) $r->nav_code,
                'label' => (string) $r->label,
                'parent_id' => $r->parent_id !== null ? (int) $r->parent_id : null,
            ])
            ->toArray();

        // build tree-ish flat for dropdown
        $byParent = [];
        foreach ($rows as $r) {
            if ($excludeId && (int) $r['id'] === (int) $excludeId) {
                continue;
            }
            $pid = $r['parent_id'] ?? 0;
            $byParent[$pid] ??= [];
            $byParent[$pid][] = $r;
        }

        $opts = ['' => '— ROOT (tanpa parent) —'];

        $walk = function ($parentId, int $depth) use (&$walk, &$opts, $byParent) {
            $children = $byParent[$parentId] ?? [];
            foreach ($children as $c) {
                $prefix = str_repeat('—', max(0, $depth));
                $opts[(string) $c['id']] = trim($prefix.' '.$c['nav_code'].' — '.$c['label']);
                $walk($c['id'], $depth + 1);
            }
        };

        $walk(0, 0);

        $this->parentOptions = $opts;
    }

    /* ===================== QUERY ===================== */
    protected function baseQuery()
    {
        $sf = in_array($this->sortField, $this->allowedSortFields, true) ? $this->sortField : 'sort_order';
        $sd = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return DB::table('auth_nav_items as n')
            ->leftJoin('auth_nav_items as p', 'p.id', '=', 'n.parent_id')
            ->leftJoin('auth_modules as m', 'm.code', '=', 'n.module_code')
            ->select([
                'n.id', 'n.nav_code', 'n.parent_id', 'n.module_code', 'n.label', 'n.route_name',
                'n.permission_code', 'n.icon', 'n.sort_order', 'n.is_active',
                DB::raw("COALESCE(p.nav_code,'') as parent_code"),
                DB::raw("COALESCE(p.label,'') as parent_label"),
                DB::raw("COALESCE(m.name,'') as module_name"),
                DB::raw('(SELECT COUNT(*) FROM auth_nav_items c WHERE c.parent_id = n.id) as children_count'),
            ])
            ->when($this->filterModule !== '', fn ($q) => $q->where('n.module_code', $this->filterModule))
            ->when($this->filterActive !== '', fn ($q) => $q->where('n.is_active', (int) $this->filterActive))
            ->orderBy("n.$sf", $sd)
            ->orderBy('n.nav_code', 'asc');
    }

    private function applySearchToQuery($q)
    {
        $s = trim($this->search);
        if ($s === '') {
            return $q;
        }

        return $q->where(function ($w) use ($s) {
            $w->where('n.nav_code', 'like', "%{$s}%")
                ->orWhere('n.label', 'like', "%{$s}%")
                ->orWhere('n.route_name', 'like', "%{$s}%")
                ->orWhere('n.permission_code', 'like', "%{$s}%");
        });
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            if (! $this->treeMode) {
                $this->resetPage();
            }

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        if (! $this->treeMode) {
            $this->resetPage();
        }
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['filterModule', 'filterActive', 'perPage', 'sortField', 'sortDirection', 'treeMode'], true)) {
            if (! $this->treeMode) {
                $this->resetPage();
            }
        }

        if ($prop === 'search') {
            if ($this->treeMode) {
                $this->autoExpandForSearch();
            } else {
                $this->resetPage();
            }
        }
    }

    /* ===================== TREE HELPERS ===================== */
    public function toggleExpand(int $id): void
    {
        $this->expanded[$id] = ! ((bool) ($this->expanded[$id] ?? false));
    }

    public function expandAll(): void
    {
        // expand all parents that have children (based on current filter)
        $rows = $this->baseQuery()->get(['n.id', DB::raw('(SELECT COUNT(*) FROM auth_nav_items c WHERE c.parent_id = n.id) as children_count')]);
        foreach ($rows as $r) {
            if ((int) $r->children_count > 0) {
                $this->expanded[(int) $r->id] = true;
            }
        }
    }

    public function collapseAll(): void
    {
        $this->expanded = [];
    }

    private function autoExpandForSearch(): void
    {
        $s = trim($this->search);
        if ($s === '') {
            return;
        }

        // ambil semua rows (tanpa search) untuk map parent chain
        $all = $this->baseQuery()->get([
            'n.id', 'n.parent_id', 'n.nav_code', 'n.label',
        ]);

        $byId = [];
        foreach ($all as $r) {
            $byId[(int) $r->id] = [
                'id' => (int) $r->id,
                'parent_id' => $r->parent_id !== null ? (int) $r->parent_id : null,
            ];
        }

        // ambil matches (dengan search)
        $matchQ = $this->applySearchToQuery($this->baseQuery());
        $matches = $matchQ->get(['n.id', 'n.parent_id']);

        // expand semua ancestor dari match
        foreach ($matches as $m) {
            $pid = $m->parent_id !== null ? (int) $m->parent_id : null;
            while ($pid !== null) {
                $this->expanded[$pid] = true;
                $pid = $byId[$pid]['parent_id'] ?? null;
            }
        }
    }

    private function buildTreeFlat(array $rows): array
    {
        // rows: array associative
        $byParent = [];
        foreach ($rows as $r) {
            $pid = $r['parent_id'] ?? 0;
            $byParent[$pid] ??= [];
            $byParent[$pid][] = $r;
        }

        $out = [];
        $visit = [];

        $walk = function ($parentId, int $depth) use (&$walk, &$out, &$visit, $byParent) {
            $children = $byParent[$parentId] ?? [];
            foreach ($children as $c) {
                $id = (int) $c['id'];
                if (isset($visit[$id])) {
                    continue;
                }
                $visit[$id] = true;

                $hasChildren = ! empty($byParent[$id] ?? []);
                $c['_depth'] = $depth;
                $c['_has_children'] = $hasChildren;

                $out[] = $c;

                if ($hasChildren && (bool) ($this->expanded[$id] ?? false)) {
                    $walk($id, $depth + 1);
                }
            }
        };

        $walk(0, 0);

        return $out;
    }

    /* ===================== MODAL HELPERS ===================== */
    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'nav_code', 'parent_id', 'module_code', 'label',
            'route_name', 'permission_code', 'icon', 'sort_order', 'is_active',
        ]);

        $this->parent_id = null;
        $this->is_active = 1;
        $this->sort_order = 0;
        $this->permission_code = '';
    }

    public function openCreate(): void
    {
        abort_unless($this->canCreate, 403, 'No permission');

        $this->resetForm();
        $this->modalMode = 'create';
        $this->editingId = null;
        $this->refreshParentOptions(null);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless($this->canUpdate, 403, 'No permission');

        $row = DB::table('auth_nav_items')->where('id', $id)->first();
        abort_unless($row, 404, 'Nav item not found');

        $this->resetForm();
        $this->modalMode = 'edit';
        $this->editingId = (int) $row->id;

        $this->nav_code = (string) $row->nav_code;
        $this->parent_id = $row->parent_id ? (int) $row->parent_id : null;
        $this->module_code = (string) $row->module_code;
        $this->label = (string) $row->label;
        $this->route_name = (string) ($row->route_name ?? '');
        $this->permission_code = (string) ($row->permission_code ?? '');
        $this->icon = (string) ($row->icon ?? '');
        $this->sort_order = (int) ($row->sort_order ?? 0);
        $this->is_active = (int) ($row->is_active ?? 1);

        $this->refreshParentOptions($this->editingId);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->refreshParentOptions($this->editingId);
    }

    /* ===================== VALIDATION HELPERS ===================== */
    private function isRouteNameInvalid(string $routeName): bool
    {
        $rn = trim($routeName);
        if ($rn === '') {
            return false;
        }

        // kadang route belum loaded ketika cache/CLI,
        // tapi di web normal harusnya valid.
        try {
            return ! Route::has($rn);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function validateNoCycle(?int $parentId, ?int $editingId): ?string
    {
        if (! $editingId || ! $parentId) {
            return null;
        }
        if ($parentId === $editingId) {
            return 'Parent tidak boleh dirinya sendiri.';
        }

        // walk up from parent -> root, if meets editingId => cycle
        $seen = [];
        $cur = $parentId;

        while ($cur) {
            if (isset($seen[$cur])) {
                break;
            } // safety
            $seen[$cur] = true;

            if ($cur === $editingId) {
                return 'Parent tidak valid (membuat siklus / cycle).';
            }

            $p = DB::table('auth_nav_items')->where('id', $cur)->value('parent_id');
            $cur = $p ? (int) $p : null;
        }

        return null;
    }

    /* ===================== SAVE ===================== */
    public function save(): void
    {
        if ($this->modalMode === 'create') {
            abort_unless($this->canCreate, 403);
        }
        if ($this->modalMode === 'edit') {
            abort_unless($this->canUpdate, 403);
        }

        $code = trim($this->nav_code);
        $label = trim($this->label);
        $module = trim($this->module_code);

        if ($code === '' || mb_strlen($code) > 50) {
            $this->toastWarn('nav_code wajib diisi (maks 50).');

            return;
        }
        if ($label === '' || mb_strlen($label) > 100) {
            $this->toastWarn('Label wajib diisi (maks 100).');

            return;
        }
        if ($module === '' || ! isset($this->moduleOptions[$module])) {
            $this->toastWarn('Module wajib valid.');

            return;
        }

        $parentId = $this->parent_id ?: null;

        // cycle check (edit)
        $cycleMsg = $this->validateNoCycle($parentId ? (int) $parentId : null, $this->editingId);
        if ($cycleMsg) {
            $this->toastWarn($cycleMsg);

            return;
        }

        // permission optional, tapi kalau diisi harus valid + harus 1 module
        $perm = trim($this->permission_code);
        if ($perm !== '') {
            if (! isset($this->permissionOptions[$perm])) {
                $this->toastWarn('Permission code tidak ditemukan di auth_permissions.');

                return;
            }
            $permMod = $this->permissionModuleMap[$perm] ?? null;
            if ($permMod !== $module) {
                $this->toastWarn("permission_code harus 1 module dengan item. Permission {$perm} milik {$permMod}, item milik {$module}.");

                return;
            }
        }

        $rn = trim($this->route_name);
        $routeName = ($rn !== '') ? $rn : null;

        // route name: kita tidak blok total (karena bisa nanti ditambahkan),
        // tapi kita kasih peringatan di toast.
        $routeInvalid = $routeName ? $this->isRouteNameInvalid($routeName) : false;

        $now = now();
        $actorId = (int) auth()->id();

        try {
            DB::transaction(function () use ($code, $parentId, $module, $label, $perm, $routeName, $now, $actorId) {

                if ($this->modalMode === 'create') {
                    $exists = DB::table('auth_nav_items')->where('nav_code', $code)->exists();
                    if ($exists) {
                        abort(422, "nav_code '{$code}' sudah ada.");
                    }

                    DB::table('auth_nav_items')->insert([
                        'nav_code' => $code,
                        'parent_id' => $parentId,
                        'module_code' => $module,
                        'label' => $label,
                        'route_name' => $routeName,
                        'permission_code' => $perm !== '' ? $perm : null,
                        'icon' => trim($this->icon) !== '' ? trim($this->icon) : null,
                        'sort_order' => (int) $this->sort_order,
                        'is_active' => (int) $this->is_active ? 1 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('auth_audit_logs')->insert([
                        'user_id' => $actorId,
                        'module_code' => '00000',
                        'action' => 'SSO_NAV_CREATE',
                        'payload' => json_encode(['nav_code' => $code], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'ip' => request()->ip(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    return;
                }

                // edit
                $id = (int) $this->editingId;
                $row = DB::table('auth_nav_items')->where('id', $id)->lockForUpdate()->first();
                abort_unless($row, 404, 'Nav item not found');

                $exists = DB::table('auth_nav_items')
                    ->where('nav_code', $code)
                    ->where('id', '<>', $id)
                    ->exists();
                if ($exists) {
                    abort(422, "nav_code '{$code}' sudah dipakai item lain.");
                }

                DB::table('auth_nav_items')->where('id', $id)->update([
                    'nav_code' => $code,
                    'parent_id' => $parentId,
                    'module_code' => $module,
                    'label' => $label,
                    'route_name' => $routeName,
                    'permission_code' => $perm !== '' ? $perm : null,
                    'icon' => trim($this->icon) !== '' ? trim($this->icon) : null,
                    'sort_order' => (int) $this->sort_order,
                    'is_active' => (int) $this->is_active ? 1 : 0,
                    'updated_at' => $now,
                ]);

                DB::table('auth_audit_logs')->insert([
                    'user_id' => $actorId,
                    'module_code' => '00000',
                    'action' => 'SSO_NAV_UPDATE',
                    'payload' => json_encode(['id' => $id, 'nav_code' => $code], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip' => request()->ip(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

            $msg = 'Menu tersimpan: '.$code;
            if ($routeInvalid) {
                $msg .= ' (⚠ route_name belum terdaftar)';
            }
            $this->toastSuccess($msg);

            $this->closeModal();
            if (! $this->treeMode) {
                $this->resetPage();
            }

            $this->primeOptions(); // refresh dropdowns
        } catch (\Throwable $e) {
            $this->toastWarn($e->getMessage());
        }
    }

    /* ===================== QUICK TOGGLE ACTIVE ===================== */
    public function toggleActive(int $id): void
    {
        abort_unless($this->canUpdate, 403);

        $now = now();
        $actorId = (int) auth()->id();

        try {
            DB::transaction(function () use ($id, $now, $actorId) {
                $row = DB::table('auth_nav_items')->where('id', $id)->lockForUpdate()->first(['id', 'nav_code', 'is_active']);
                abort_unless($row, 404, 'Nav item not found');

                $new = ((int) $row->is_active === 1) ? 0 : 1;

                DB::table('auth_nav_items')->where('id', $id)->update([
                    'is_active' => $new,
                    'updated_at' => $now,
                ]);

                DB::table('auth_audit_logs')->insert([
                    'user_id' => $actorId,
                    'module_code' => '00000',
                    'action' => 'SSO_NAV_TOGGLE_ACTIVE',
                    'payload' => json_encode(['id' => $id, 'nav_code' => $row->nav_code, 'is_active' => $new], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip' => request()->ip(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

            $this->toastSuccess('Status menu berhasil diubah.');
            $this->primeOptions();
        } catch (\Throwable $e) {
            $this->toastWarn($e->getMessage());
        }
    }

    /* ===================== DELETE (HARD) ===================== */
    public function openDelete(int $id): void
    {
        abort_unless($this->canDelete, 403);

        $this->deletingId = $id;
        $this->deleteConfirm = '';
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->reset(['showDeleteModal', 'deletingId', 'deleteConfirm']);
    }

    public function confirmDelete(): void
    {
        abort_unless($this->canDelete, 403);

        $id = (int) ($this->deletingId ?? 0);
        if ($id <= 0) {
            return;
        }

        try {
            DB::transaction(function () use ($id) {
                $row = DB::table('auth_nav_items')->where('id', $id)->lockForUpdate()->first(['id', 'nav_code']);
                abort_unless($row, 404, 'Nav item not found');

                $expect = (string) $row->nav_code;
                if (trim($this->deleteConfirm) !== $expect) {
                    abort(422, "Konfirmasi salah. Ketik tepat: {$expect}");
                }

                // FK parent_id ON DELETE CASCADE => anak ikut terhapus
                DB::table('auth_nav_items')->where('id', $id)->delete();

                DB::table('auth_audit_logs')->insert([
                    'user_id' => (int) auth()->id(),
                    'module_code' => '00000',
                    'action' => 'SSO_NAV_DELETE',
                    'payload' => json_encode(['id' => $id, 'nav_code' => $expect], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->toastSuccess('Menu berhasil dihapus.');
            $this->cancelDelete();
            $this->primeOptions();
            if (! $this->treeMode) {
                $this->resetPage();
            }
        } catch (\Throwable $e) {
            $this->toastWarn($e->getMessage());
        }
    }

    /* ===================== RENDER ===================== */
    public function render()
    {
        if (! $this->treeMode) {
            // LIST MODE (paginate)
            $q = $this->applySearchToQuery($this->baseQuery());
            $rows = $q->paginate($this->perPage);

            return view('livewire.auth.sso.nav.sso-nav-item-table', [
                'mode' => 'list',
                'rows' => $rows,
                'flatRows' => [],
                'total' => (int) $rows->total(),
            ])->layout('components.sccr-layout');
        }

        // TREE MODE (no paginate)
        $qAll = $this->baseQuery(); // no search
        $allRows = $qAll->get()->map(fn ($r) => [
            'id' => (int) $r->id,
            'nav_code' => (string) $r->nav_code,
            'parent_id' => $r->parent_id !== null ? (int) $r->parent_id : null,
            'module_code' => (string) $r->module_code,
            'module_name' => (string) ($r->module_name ?? ''),
            'label' => (string) $r->label,
            'route_name' => $r->route_name ? (string) $r->route_name : null,
            'permission_code' => $r->permission_code ? (string) $r->permission_code : null,
            'icon' => $r->icon ? (string) $r->icon : null,
            'sort_order' => (int) $r->sort_order,
            'is_active' => (int) $r->is_active,
            'children_count' => (int) ($r->children_count ?? 0),
            'parent_code' => (string) ($r->parent_code ?? ''),
            'parent_label' => (string) ($r->parent_label ?? ''),
        ])->toArray();

        $total = count($allRows);

        // kalau search, tampilkan hanya match + ancestors agar tree konteks jelas
        $s = trim($this->search);
        if ($s !== '') {
            $byId = [];
            foreach ($allRows as $r) {
                $byId[(int) $r['id']] = $r;
            }

            $matchQ = $this->applySearchToQuery($this->baseQuery());
            $matchIds = $matchQ->get(['n.id'])->pluck('id')->map(fn ($x) => (int) $x)->toArray();

            $keep = [];
            foreach ($matchIds as $id) {
                $keep[$id] = true;
                $pid = $byId[$id]['parent_id'] ?? null;
                while ($pid !== null) {
                    $keep[$pid] = true;
                    $pid = $byId[$pid]['parent_id'] ?? null;
                }
            }

            $allRows = array_values(array_filter($allRows, fn ($r) => isset($keep[(int) $r['id']])));
        }

        $flat = $this->buildTreeFlat($allRows);

        return view('livewire.auth.sso.nav.sso-nav-item-table', [
            'mode' => 'tree',
            'rows' => null,
            'flatRows' => $flat,
            'total' => $total,
        ])->layout('components.sccr-layout');
    }
}
