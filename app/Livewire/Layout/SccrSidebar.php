<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class SccrSidebar extends Component
{
    public string $go = '';

    public string $menuSearch = '';

    /** @var array<string,bool> module_code => expanded */
    public array $expandedModules = [];

    /** @var array<string,bool> "module|perm" => expanded */
    public array $expandedPerms = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    // ✅ penting: force re-mount toast agar pasti tampil
    public int $toastSeq = 0;

    public function mount(): void
    {
        $this->expandedModules = session()->get('sccr.sidebar.expandedModules', []);
        $this->expandedPerms = session()->get('sccr.sidebar.expandedPerms', []);

        if (empty($this->expandedModules)) {
            $this->expandedModules = ['00000' => true];
            session()->put('sccr.sidebar.expandedModules', $this->expandedModules);
        }
    }

    private function pushToast(string $type, string $message): void
    {
        $this->toast = ['show' => true, 'type' => $type, 'message' => $message];
        $this->toastSeq++; // ✅ re-mount toast component
    }

    /* ===================== GO TO ===================== */
    public function goModule()
    {
        $u = auth()->user();
        if (! $u) {
            return null;
        }

        $q = trim($this->go);
        if ($q === '') {
            return null;
        }

        $key = strtoupper(str_replace(' ', '-', $q));
        $short = [
            'SSO' => 'dashboard.sso',
            'SSO-USERS' => 'sso.users.table',
            'SSO-ROLES' => 'sso.roles.table',
            'APPROVAL' => 'sso.approvals.inbox',
        ];
        if (isset($short[$key]) && Route::has($short[$key])) {
            return redirect()->route($short[$key]);
        }

        // module code 5 digit
        if (preg_match('/^\d{5}$/', $q)) {

            // 1) cek module ada dulu
            $mod = DB::table('auth_modules')->where('code', $q)->first(['route', 'is_active']);
            if (! $mod) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Module code {$q} tidak diketemukan."];

                return null;
            }
            if ((int) $mod->is_active !== 1) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Module {$q} tidak aktif."];

                return null;
            }

            // 2) baru cek akses
            if (! $u->isSuperAdmin() && ! $u->hasModule($q)) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Anda tidak memiliki hak akses untuk module {$q}."];

                return null;
            }

            // 3) cek route
            $route = trim((string) ($mod->route ?? ''));
            if ($route === '') {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Module {$q} belum punya route."];

                return null;
            }

            if (! Route::has($route)) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => "Route tidak terdaftar: {$route}"];

                return null;
            }

            return redirect()->route($route);
        }

        $this->toast = [
            'show' => true,
            'type' => 'warning',
            'message' => 'Masukkan module code 5 digit (contoh 01005) atau shortcut (SSO / SSO-USERS / SSO-ROLES / APPROVAL).',
        ];

        return null;
    }

    /* ===================== FAVORITES ===================== */
    public function toggleFavorite(int $moduleId): void
    {
        $u = auth()->user();
        if (! $u) {
            return;
        }

        $mod = DB::table('auth_modules')->where('id', $moduleId)->first(['id', 'code', 'name']);
        if (! $mod) {
            return;
        }

        if (! $u->isSuperAdmin() && ! $u->hasModule((string) $mod->code)) {
            $this->pushToast('warning', "Anda tidak memiliki hak akses untuk module {$mod->code}.");

            return;
        }

        $exists = DB::table('auth_user_favorite_modules')
            ->where('user_id', (int) $u->id)
            ->where('module_id', (int) $moduleId)
            ->exists();

        if ($exists) {
            DB::table('auth_user_favorite_modules')
                ->where('user_id', (int) $u->id)
                ->where('module_id', (int) $moduleId)
                ->delete();

            $this->pushToast('success', "Favorite dihapus: {$mod->name}");

            return;
        }

        DB::table('auth_user_favorite_modules')->insert([
            'user_id' => (int) $u->id,
            'module_id' => (int) $moduleId,
        ]);

        $this->pushToast('success', "Favorite ditambah: {$mod->name}");
    }

    /* ===================== EXPAND/COLLAPSE ===================== */
    public function toggleModuleExpand(string $moduleCode): void
    {
        $mc = trim($moduleCode);
        if ($mc === '') {
            return;
        }

        $this->expandedModules[$mc] = ! ((bool) ($this->expandedModules[$mc] ?? false));
        session()->put('sccr.sidebar.expandedModules', $this->expandedModules);
    }

    public function togglePermExpand(string $moduleCode, string $permCode): void
    {
        $mc = trim($moduleCode);
        $pc = trim($permCode); // boleh kosong
        if ($mc === '') {
            return;
        }

        $k = $mc.'|'.$pc;
        $this->expandedPerms[$k] = ! ((bool) ($this->expandedPerms[$k] ?? false));
        session()->put('sccr.sidebar.expandedPerms', $this->expandedPerms);
    }

    public function expandAllModules(): void
    {
        foreach ($this->modulesForUser() as $m) {
            $this->expandedModules[$m['code']] = true;
        }
        session()->put('sccr.sidebar.expandedModules', $this->expandedModules);
    }

    public function collapseAllModules(): void
    {
        $this->expandedModules = [];
        session()->put('sccr.sidebar.expandedModules', $this->expandedModules);
    }

    /* ===================== DATA HELPERS ===================== */
    private function modulesForUser(): array
    {
        $u = auth()->user();
        if (! $u) {
            return [];
        }

        $codes = $u->isSuperAdmin()
            ? DB::table('auth_modules')->where('is_active', 1)->pluck('code')->toArray()
            : $u->modules();

        return DB::table('auth_modules')
            ->where('is_active', 1)
            ->whereIn('code', $codes)
            ->orderBy('code', 'asc')
            ->get(['id', 'code', 'name', 'route', 'icon'])
            ->map(fn ($m) => [
                'id' => (int) $m->id,
                'code' => (string) $m->code,
                'name' => (string) $m->name,
                'route' => (string) ($m->route ?? ''),
                'icon' => (string) ($m->icon ?? ''),
            ])
            ->toArray();
    }

    private function favoriteIdsForUser(): array
    {
        $u = auth()->user();
        if (! $u) {
            return [];
        }

        return DB::table('auth_user_favorite_modules')
            ->where('user_id', (int) $u->id)
            ->pluck('module_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();
    }

    private function permissionCodesForUser(): array
    {
        $u = auth()->user();
        if (! $u) {
            return [];
        }

        if ($u->isSuperAdmin()) {
            return DB::table('auth_permissions')
                ->where('is_active', 1)
                ->pluck('code')
                ->map(fn ($x) => (string) $x)
                ->toArray();
        }

        // user -> roles -> role_permissions -> permissions
        return DB::table('auth_user_roles as ur')
            ->join('auth_role_permissions as rp', 'rp.role_id', '=', 'ur.role_id')
            ->join('auth_permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('ur.auth_user_id', (int) $u->id)
            ->where('p.is_active', 1)
            ->distinct()
            ->orderBy('p.code', 'asc')
            ->pluck('p.code')
            ->map(fn ($x) => (string) $x)
            ->toArray();
    }

    /**
     * Build sidebar tree: Module -> Permission -> Nav items
     */
    private function buildSidebarTree(array $modules): array
    {
        $u = auth()->user();
        if (! $u || empty($modules)) {
            return [];
        }

        $moduleCodes = array_map(fn ($m) => $m['code'], $modules);
        $moduleMap = [];
        foreach ($modules as $m) {
            $moduleMap[$m['code']] = $m;
        }

        $userPerms = $this->permissionCodesForUser();
        $permSet = array_fill_keys($userPerms, true);

        // permission meta (desc + requires_approval) untuk module yg user punya
        $permMetaRows = DB::table('auth_permissions')
            ->where('is_active', 1)
            ->whereIn('module_code', $moduleCodes)
            ->get(['code', 'module_code', 'description', 'requires_approval'])
            ->map(fn ($p) => [
                'code' => (string) $p->code,
                'module_code' => (string) $p->module_code,
                'description' => (string) ($p->description ?? ''),
                'requires_approval' => (int) ($p->requires_approval ?? 0),
            ])
            ->toArray();

        $permMeta = []; // [module][code] => meta
        foreach ($permMetaRows as $p) {
            $permMeta[$p['module_code']][$p['code']] = $p;
        }

        // nav items: filter by module + is_active
        $navRows = DB::table('auth_nav_items')
            ->where('is_active', 1)
            ->whereIn('module_code', $moduleCodes)
            ->orderBy('module_code', 'asc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('nav_code', 'asc')
            ->get([
                'id', 'nav_code', 'parent_id', 'module_code', 'label', 'route_name', 'permission_code', 'icon', 'sort_order',
            ])
            ->map(fn ($n) => [
                'id' => (int) $n->id,
                'nav_code' => (string) $n->nav_code,
                'parent_id' => $n->parent_id !== null ? (int) $n->parent_id : null,
                'module_code' => (string) $n->module_code,
                'label' => (string) $n->label,
                'route_name' => (string) ($n->route_name ?? ''),
                'permission_code' => (string) ($n->permission_code ?? ''),
                'icon' => (string) ($n->icon ?? ''),
                'sort_order' => (int) ($n->sort_order ?? 0),
            ])
            ->toArray();

        // Group nav items per module first
        $navByModule = [];
        foreach ($navRows as $r) {
            // gate permission
            $pc = trim($r['permission_code']);
            if ($pc !== '' && ! $u->isSuperAdmin() && ! isset($permSet[$pc])) {
                continue;
            }
            $navByModule[$r['module_code']][] = $r;
        }

        $q = trim(mb_strtolower($this->menuSearch, 'UTF-8'));

        $out = [];

        foreach ($moduleCodes as $mc) {
            $mod = $moduleMap[$mc] ?? null;
            if (! $mod) {
                continue;
            }

            $items = $navByModule[$mc] ?? [];
            if (empty($items)) {
                continue;
            }

            // build breadcrumb path label (optional, untuk info detail)
            $byId = [];
            foreach ($items as $it) {
                $byId[$it['id']] = $it;
            }

            $pathMemo = [];
            $pathOf = function (int $id) use (&$pathOf, &$pathMemo, &$byId): string {
                if (isset($pathMemo[$id])) {
                    return $pathMemo[$id];
                }
                if (! isset($byId[$id])) {
                    return $pathMemo[$id] = '';
                }
                $p = $byId[$id]['parent_id'] ?? null;
                if ($p === null || ! isset($byId[$p])) {
                    return $pathMemo[$id] = $byId[$id]['label'];
                }
                $parentPath = $pathOf((int) $p);

                return $pathMemo[$id] = ($parentPath !== '' ? ($parentPath.' › ') : '').$byId[$id]['label'];
            };

            // group by permission_code ('' => No Permission)
            $grp = []; // perm => items[]
            foreach ($items as $it) {
                $perm = trim($it['permission_code']);
                $it['path'] = $pathOf($it['id']);

                // clickable hanya kalau route valid
                $rn = trim($it['route_name']);
                $it['route_ok'] = ($rn !== '' && Route::has($rn));

                // untuk sidebar navigasi, skip item yg route invalid dan tidak punya anak link valid.
                // (biar ga ada dead link)
                if (! $it['route_ok'] && $rn !== '') {
                    continue;
                }

                $grp[$perm][] = $it;
            }

            if (empty($grp)) {
                continue;
            }

            // build permission groups output
            $permGroups = [];

            foreach ($grp as $pc => $list) {
                // search filter per group
                $meta = $permMeta[$mc][$pc] ?? null;
                $pDesc = $meta['description'] ?? '';
                $pReq = (int) ($meta['requires_approval'] ?? 0);

                // filter items by search (kalau search aktif)
                $filtered = $list;
                if ($q !== '') {
                    $filtered = array_values(array_filter($list, function ($it) use ($q, $pc, $pDesc) {
                        $hay = mb_strtolower(
                            ($it['nav_code'] ?? '').' '.($it['label'] ?? '').' '.($it['route_name'] ?? '').' '.($it['permission_code'] ?? '').' '.($it['path'] ?? '').' '.$pc.' '.$pDesc,
                            'UTF-8'
                        );

                        return mb_strpos($hay, $q) !== false;
                    }));

                    // kalau tidak ada item match, tapi permission/meta match -> tetap tampilkan
                    $permHay = mb_strtolower(($pc ?: 'no permission').' '.$pDesc, 'UTF-8');
                    $permMatch = mb_strpos($permHay, $q) !== false;

                    if (empty($filtered) && ! $permMatch) {
                        continue;
                    }
                }

                // open state
                $k = $mc.'|'.$pc;
                $autoOpen = ($q !== '');
                $open = (bool) ($this->expandedPerms[$k] ?? false) || $autoOpen;

                $permGroups[] = [
                    'code' => $pc,
                    'label' => $pc !== '' ? $pc : 'No Permission',
                    'description' => (string) $pDesc,
                    'requires_approval' => $pReq,
                    'open' => $open,
                    'count' => count($filtered),
                    'items' => $filtered,
                ];
            }

            if (empty($permGroups)) {
                continue;
            }

            // module search match: kalau search match module code/name, tampilkan semua permGroups (yang sudah terfilter by item match di atas)
            $modMatch = false;
            if ($q !== '') {
                $modHay = mb_strtolower($mc.' '.$mod['name'], 'UTF-8');
                $modMatch = mb_strpos($modHay, $q) !== false;
                // kalau module match, kita tidak perlu apa-apa khusus; permGroups sudah OK.
            }

            $autoOpenMod = ($q !== '');
            $openMod = (bool) ($this->expandedModules[$mc] ?? false) || $autoOpenMod;

            $out[] = [
                'id' => (int) $mod['id'],
                'code' => $mc,
                'name' => (string) $mod['name'],
                'route' => (string) ($mod['route'] ?? ''),
                'icon' => (string) ($mod['icon'] ?? ''),
                'open' => $openMod,
                'perms' => $permGroups,
            ];
        }

        return $out;
    }

    public function render()
    {
        $modules = $this->modulesForUser();

        // merge favorite flags
        $favIds = $this->favoriteIdsForUser();
        $favSet = array_fill_keys($favIds, true);

        $modules = array_map(function ($m) use ($favSet) {
            $m['is_favorite'] = isset($favSet[(int) $m['id']]);

            return $m;
        }, $modules);

        $favorites = array_values(array_filter($modules, fn ($m) => ! empty($m['is_favorite'])));

        return view('livewire.layout.sccr-sidebar', [
            'modules' => $modules,
            'favorites' => $favorites,
            'menuTree' => $this->buildSidebarTree($modules),
        ]);
    }
}
