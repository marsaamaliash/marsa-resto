<?php

namespace App\Livewire\Bod;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class InventarisDashboard extends Component
{
    // Inventaris masih keluarga SDM
    private const MODULE_CODE_BOD_SDM = '00002';

    /** UI */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /** Caps */
    public bool $canView = false;

    /** Filters */
    public string $filterHolding = '';   // i.ab

    public string $filterLokasi = '';    // i.cd

    public string $filterRuangan = '';   // i.ef

    public string $filterJenis = '';     // i.gh

    public string $filterStatus = '';    // ''|Baik|Rusak|Hilang|Dalam Perbaikan

    public string $filterLifecycle = ''; // ''|active|pending_delete|inactive

    /** Range (YYYY-MM) */
    public string $fromMonth = '';

    public string $toMonth = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'BoD', 'route' => 'bod.inventaris.dashboard', 'color' => 'text-white'],
            ['label' => 'Inventaris Dashboard', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();

        if (! $this->canView) {
            abort(403, 'Forbidden');
        }

        // default: bulan berjalan (awal bulan ini s/d now)
        $this->resetRange(false);
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canView = (bool) (
            ($u?->isSuperAdmin() ?? false)
            || $this->userHasAnyPermission($u, ['BOD_INV_DASH_VIEW', 'INV_VIEW', 'INVENTARIS_VIEW'])
            || $this->userHasModuleAccess($u, self::MODULE_CODE_BOD_SDM)
        );
    }

    private function userHasAnyPermission($user, array $permissions): bool
    {
        if (! $user) {
            return false;
        }

        foreach ($permissions as $permission) {
            try {
                if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return false;
    }

    private function userHasModuleAccess($user, string $moduleCode): bool
    {
        if (! $user || $moduleCode === '') {
            return false;
        }

        foreach (['hasModuleAccess', 'hasModule', 'canAccessModule'] as $method) {
            try {
                if (method_exists($user, $method) && (bool) $user->{$method}($moduleCode)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (! Schema::hasTable('auth_role_modules')) {
            return false;
        }

        $roleIds = [];

        if (isset($user->role_id) && $user->role_id !== null && $user->role_id !== '') {
            $roleIds[] = (int) $user->role_id;
        }

        try {
            if (method_exists($user, 'roles')) {
                $roleIds = array_merge(
                    $roleIds,
                    $user->roles()->pluck('id')->map(fn ($id) => (int) $id)->all()
                );
            } elseif (isset($user->roles) && is_iterable($user->roles)) {
                foreach ($user->roles as $role) {
                    if (isset($role->id)) {
                        $roleIds[] = (int) $role->id;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $roleIds = array_values(array_unique(array_filter($roleIds, fn ($id) => $id > 0)));
        if (empty($roleIds)) {
            return false;
        }

        return DB::table('auth_role_modules')
            ->whereIn('role_id', $roleIds)
            ->where('module_code', $moduleCode)
            ->where('is_active', 1)
            ->exists();
    }

    public function updated($property): void
    {
        if ($property === 'filterHolding') {
            $this->filterLokasi = '';
            $this->filterRuangan = '';
        }

        if ($property === 'filterLokasi') {
            $this->filterRuangan = '';
        }
    }

    /* =========================
     * ACTIONS (RANGE)
     * ========================= */
    public function applyRange(): void
    {
        [$this->fromMonth, $this->toMonth] = $this->rangeYm();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Periode dashboard diterapkan.',
        ];

        $this->dispatchChartsRefresh();
    }

    public function resetRange(bool $withToast = true): void
    {
        $now = Carbon::now();
        $this->fromMonth = $now->format('Y-m');
        $this->toMonth = $now->format('Y-m');

        if ($withToast) {
            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => 'Periode dashboard direset ke bulan berjalan.',
            ];
        }

        $this->dispatchChartsRefresh();
    }

    private function dispatchChartsRefresh(): void
    {
        // Livewire v2
        if (method_exists($this, 'dispatchBrowserEvent')) {
            $this->dispatchBrowserEvent('bod-inv-charts-refresh');

            return;
        }

        // Livewire v3
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('bod-inv-charts-refresh');
        }
    }

    /* =========================
     * HELPERS
     * ========================= */
    protected function invCollate(string $expr): string
    {
        return DB::getDriverName() === 'mysql'
            ? "$expr COLLATE utf8mb4_unicode_ci"
            : $expr;
    }

    protected function monthExpr(string $col): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            return "DATE_FORMAT($col, '%Y-%m')";
        }
        if ($driver === 'pgsql') {
            return "to_char($col, 'YYYY-MM')";
        }

        return "to_char($col, 'YYYY-MM')";
    }

    /**
     * Bulan/Tahun -> YYYY-MM
     */
    protected function acquisitionMonthExpr(): ?string
    {
        if (! Schema::hasColumn('inventaris', 'Bulan') || ! Schema::hasColumn('inventaris', 'Tahun')) {
            return null;
        }

        if (DB::getDriverName() === 'mysql') {
            return "CONCAT(LPAD(CASE WHEN i.Tahun < 100 THEN (2000 + i.Tahun) ELSE i.Tahun END, 4, '0'), '-', LPAD(i.Bulan, 2, '0'))";
        }

        return null;
    }

    protected function rangeYm(): array
    {
        $now = Carbon::now();
        $from = trim((string) ($this->fromMonth ?: $now->format('Y-m')));
        $to = trim((string) ($this->toMonth ?: $now->format('Y-m')));

        try {
            $fromC = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
        } catch (\Throwable $e) {
            $fromC = $now->copy()->startOfMonth();
        }

        try {
            $toC = Carbon::createFromFormat('Y-m', $to)->startOfMonth();
        } catch (\Throwable $e) {
            $toC = $now->copy()->startOfMonth();
        }

        if ($fromC->greaterThan($toC)) {
            [$fromC, $toC] = [$toC, $fromC];
        }

        return [$fromC->format('Y-m'), $toC->format('Y-m')];
    }

    protected function monthRange(): array
    {
        [$fromYm, $toYm] = $this->rangeYm();

        $start = Carbon::createFromFormat('Y-m', $fromYm)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $toYm)->startOfMonth();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $out = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $out[] = $cur->format('Y-m');
            $cur->addMonth();
        }

        return $out;
    }

    /* =========================
     * OPTIONS
     * ========================= */
    protected function holdingOptions(): array
    {
        if (Schema::hasTable('v_inv_holding_list')) {
            return DB::table('v_inv_holding_list')
                ->orderBy('label_holding')
                ->get(['kode', 'label_holding'])
                ->mapWithKeys(fn ($r) => [(string) $r->kode => (string) $r->label_holding])
                ->toArray();
        }

        return DB::table('holdings')
            ->whereNotNull('inv_code')
            ->orderBy('alias')
            ->get(['inv_code', 'alias', 'name'])
            ->mapWithKeys(fn ($r) => [
                (string) $r->inv_code => trim(((string) $r->inv_code.' - '.(string) $r->alias), ' -'),
            ])
            ->toArray();
    }

    protected function lokasiOptions(): array
    {
        if (Schema::hasTable('v_inv_lokasi_list')) {
            $q = DB::table('v_inv_lokasi_list')->orderBy('label_lokasi');

            if ($this->filterHolding !== '') {
                $q->where('holding_kode', $this->filterHolding);
            }

            return $q->get(['lokasi_kode', 'label_lokasi'])
                ->mapWithKeys(fn ($r) => [(string) $r->lokasi_kode => (string) $r->label_lokasi])
                ->toArray();
        }

        $q = DB::table('inv_lokasi')
            ->whereNull('deleted_at')
            ->where('lifecycle_status', '<>', 'deleted')
            ->orderBy('lokasi');

        if ($this->filterHolding !== '') {
            $q->where('holding_kode', $this->filterHolding);
        }

        return $q->get(['kode', 'lokasi'])
            ->mapWithKeys(fn ($r) => [(string) $r->kode => (string) $r->lokasi])
            ->toArray();
    }

    protected function ruanganOptions(): array
    {
        if (Schema::hasTable('v_inv_ruangan_list')) {
            $q = DB::table('v_inv_ruangan_list')
                ->orderBy('holding_kode')
                ->orderBy('lokasi_kode')
                ->orderBy('kode_ruangan');

            if ($this->filterHolding !== '') {
                $q->where('holding_kode', $this->filterHolding);
            }
            if ($this->filterLokasi !== '') {
                $q->where('lokasi_kode', $this->filterLokasi);
            }

            return $q->get(['kode_ruangan', 'label_ruangan'])
                ->mapWithKeys(fn ($r) => [(string) $r->kode_ruangan => (string) $r->label_ruangan])
                ->toArray();
        }

        $q = DB::table('inv_ruangan')
            ->orderBy('holding_kode')
            ->orderBy('lokasi_kode')
            ->orderBy('kode');

        if ($this->filterHolding !== '') {
            $q->where('holding_kode', $this->filterHolding);
        }
        if ($this->filterLokasi !== '') {
            $q->where('lokasi_kode', $this->filterLokasi);
        }

        return $q->get(['kode', 'nama_ruang'])
            ->mapWithKeys(fn ($r) => [(string) $r->kode => (string) $r->nama_ruang])
            ->toArray();
    }

    protected function jenisOptions(): array
    {
        if (Schema::hasTable('v_inv_jenis_list')) {
            return DB::table('v_inv_jenis_list')
                ->orderBy('label_jenis')
                ->get(['jenis_kode', 'label_jenis'])
                ->mapWithKeys(fn ($r) => [(string) $r->jenis_kode => (string) $r->label_jenis])
                ->toArray();
        }

        return DB::table('inv_jenis_barang')
            ->orderBy('jenis_barang')
            ->get(['kode', 'jenis_barang'])
            ->mapWithKeys(fn ($r) => [(string) $r->kode => (string) $r->jenis_barang])
            ->toArray();
    }

    protected function statusOptions(): array
    {
        return [
            '' => 'Semua Status',
            'Baik' => 'Baik',
            'Rusak' => 'Rusak',
            'Hilang' => 'Hilang',
            'Dalam Perbaikan' => 'Dalam Perbaikan',
        ];
    }

    protected function lifecycleOptions(): array
    {
        return [
            '' => 'Semua Lifecycle',
            'active' => 'Active',
            'pending_delete' => 'Pending Delete',
            'inactive' => 'Inactive',
        ];
    }

    /* =========================
     * QUERY (BASE)
     * ========================= */
    protected function inventarisBaseQuery()
    {
        $q = DB::table('inventaris as i')
            ->leftJoin('holdings as h', function ($join) {
                $join->on(DB::raw($this->invCollate('i.ab')), '=', DB::raw($this->invCollate('h.inv_code')));
            })
            ->leftJoin('inv_lokasi as l', function ($join) {
                $join->on(DB::raw($this->invCollate('i.ab')), '=', DB::raw($this->invCollate('l.holding_kode')));
                $join->on(DB::raw($this->invCollate('i.cd')), '=', DB::raw($this->invCollate('l.kode')));
            })
            ->leftJoin('inv_ruangan as r', function ($join) {
                $join->on(DB::raw($this->invCollate('i.ab')), '=', DB::raw($this->invCollate('r.holding_kode')));
                $join->on(DB::raw($this->invCollate('i.cd')), '=', DB::raw($this->invCollate('r.lokasi_kode')));
                $join->on(DB::raw($this->invCollate('i.ef')), '=', DB::raw($this->invCollate('r.kode')));
            })
            ->leftJoin('inv_jenis_barang as j', function ($join) {
                $join->on(DB::raw($this->invCollate('i.gh')), '=', DB::raw($this->invCollate('j.kode')));
            });

        // soft delete / lifecycle guards
        $q->whereNull('i.deleted_at');

        if (Schema::hasColumn('inventaris', 'lifecycle_status')) {
            $q->where(function ($w) {
                $w->whereNull('i.lifecycle_status')
                    ->orWhere('i.lifecycle_status', '<>', 'deleted');
            });
        }

        // RANGE FILTER (seperti employee): inventaris yang "acquired" di periode Bulan/Tahun
        $acqExpr = $this->acquisitionMonthExpr();
        if ($acqExpr) {
            [$fromYm, $toYm] = $this->rangeYm();
            $q->whereNotNull('i.Bulan')
                ->whereNotNull('i.Tahun')
                ->where('i.Bulan', '>=', 1)
                ->where('i.Bulan', '<=', 12)
                ->where('i.Tahun', '>', 0)
                ->whereRaw("$acqExpr >= ? AND $acqExpr <= ?", [$fromYm, $toYm]);
        }

        // filters
        if ($this->filterHolding !== '') {
            $q->where('i.ab', $this->filterHolding);
        }
        if ($this->filterLokasi !== '') {
            $q->where('i.cd', $this->filterLokasi);
        }
        if ($this->filterRuangan !== '') {
            $q->where('i.ef', $this->filterRuangan);
        }
        if ($this->filterJenis !== '') {
            $q->where('i.gh', $this->filterJenis);
        }
        if ($this->filterStatus !== '') {
            $q->where('i.status', $this->filterStatus);
        }
        if ($this->filterLifecycle !== '') {
            $q->where('i.lifecycle_status', $this->filterLifecycle);
        }

        return $q;
    }

    /* =========================
     * METRICS
     * ========================= */
    protected function buildMetrics(): array
    {
        $base = $this->inventarisBaseQuery();

        $total = (clone $base)->count('i.kode_label');

        $baik = (clone $base)->where('i.status', 'Baik')->count('i.kode_label');

        $rusak = (clone $base)
            ->whereIn('i.status', ['Rusak', 'Dalam Perbaikan'])
            ->count('i.kode_label');

        $hilang = (clone $base)->where('i.status', 'Hilang')->count('i.kode_label');

        $inactive = 0;
        $pendingDelete = 0;

        if (Schema::hasColumn('inventaris', 'lifecycle_status')) {
            $inactive = (clone $base)->where('i.lifecycle_status', 'inactive')->count('i.kode_label');
            $pendingDelete = (clone $base)->where('i.lifecycle_status', 'pending_delete')->count('i.kode_label');
        }

        // month reference = toMonth (bukan now)
        [$_fromYm, $toYm] = $this->rangeYm();

        // barang masuk bulan ini (berdasarkan Bulan/Tahun)
        $addedThisMonth = 0;
        $acqExpr = $this->acquisitionMonthExpr();
        if ($acqExpr) {
            $addedThisMonth = (clone $base)
                ->whereRaw("$acqExpr = ?", [$toYm])
                ->count('i.kode_label');
        }

        // perubahan status bulan ini (berdasarkan tanggal_status)
        $statusUpdatedThisMonth = 0;
        if (Schema::hasColumn('inventaris', 'tanggal_status')) {
            $statusUpdatedThisMonth = (clone $base)
                ->whereNotNull('i.tanggal_status')
                ->whereRaw($this->monthExpr('i.tanggal_status').' = ?', [$toYm])
                ->count('i.kode_label');
        }

        return [
            'total' => $total,
            'baik' => $baik,
            'rusak' => $rusak,
            'hilang' => $hilang,
            'inactive' => $inactive,
            'pending_delete' => $pendingDelete,
            'added_this_month' => $addedThisMonth,
            'status_updated_this_month' => $statusUpdatedThisMonth,
        ];
    }

    /* =========================
     * CHARTS
     * ========================= */
    protected function buildCharts(): array
    {
        $months = $this->monthRange();
        $base = $this->inventarisBaseQuery();

        // added per month (Bulan/Tahun)
        $added = array_fill_keys($months, 0);
        $acqExpr = $this->acquisitionMonthExpr();

        if ($acqExpr) {
            $rows = (clone $base)
                ->selectRaw("$acqExpr as ym, COUNT(i.kode_label) as c")
                ->whereRaw("$acqExpr >= ? AND $acqExpr <= ?", [$months[0], $months[count($months) - 1]])
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->get();

            foreach ($rows as $r) {
                $ym = (string) $r->ym;
                if (isset($added[$ym])) {
                    $added[$ym] = (int) $r->c;
                }
            }
        }

        // status updates per month (tanggal_status)
        $statusUpdates = array_fill_keys($months, 0);
        if (Schema::hasColumn('inventaris', 'tanggal_status')) {
            $expr = $this->monthExpr('i.tanggal_status');

            $rows = (clone $base)
                ->selectRaw("$expr as ym, COUNT(i.kode_label) as c")
                ->whereNotNull('i.tanggal_status')
                ->whereRaw("$expr >= ? AND $expr <= ?", [$months[0], $months[count($months) - 1]])
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->get();

            foreach ($rows as $r) {
                $ym = (string) $r->ym;
                if (isset($statusUpdates[$ym])) {
                    $statusUpdates[$ym] = (int) $r->c;
                }
            }
        }

        // by holding
        $byHolding = (clone $base)
            ->selectRaw("COALESCE(h.alias,'-') as holding_alias, COALESCE(h.name,'-') as holding_name, COUNT(i.kode_label) as c")
            ->groupBy('holding_alias', 'holding_name')
            ->orderByDesc('c')
            ->limit(8)
            ->get()
            ->map(fn ($r) => [
                'label' => trim(((string) $r->holding_alias).' - '.((string) $r->holding_name)),
                'count' => (int) $r->c,
            ])->toArray();

        // status distribution
        $statusRows = (clone $base)
            ->selectRaw("COALESCE(i.status,'(EMPTY)') as st, COUNT(i.kode_label) as c")
            ->groupBy('st')
            ->orderByDesc('c')
            ->get();

        $status = [];
        foreach ($statusRows as $r) {
            $label = (string) $r->st;
            if ($label === '(EMPTY)') {
                $label = 'UNKNOWN';
            }
            $status[] = ['label' => $label, 'count' => (int) $r->c];
        }

        // lifecycle distribution
        $lifecycle = [];
        if (Schema::hasColumn('inventaris', 'lifecycle_status')) {
            $lifeRows = (clone $base)
                ->selectRaw("COALESCE(i.lifecycle_status,'(EMPTY)') as st, COUNT(i.kode_label) as c")
                ->groupBy('st')
                ->orderByDesc('c')
                ->get();

            foreach ($lifeRows as $r) {
                $label = (string) $r->st;
                if ($label === '(EMPTY)') {
                    $label = 'UNKNOWN';
                }
                $lifecycle[] = ['label' => $label, 'count' => (int) $r->c];
            }
        }

        return [
            'months' => $months,
            'added' => array_values($added),
            'status_updates' => array_values($statusUpdates),
            'by_holding' => $byHolding,
            'status' => $status,
            'lifecycle' => $lifecycle,
        ];
    }

    protected function buildTopJenis(): array
    {
        return $this->inventarisBaseQuery()
            ->selectRaw("COALESCE(j.jenis_barang,'-') as jenis_name, COUNT(i.kode_label) as c")
            ->groupBy('jenis_name')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['jenis' => (string) $r->jenis_name, 'count' => (int) $r->c])
            ->toArray();
    }

    protected function buildTopLokasi(): array
    {
        return $this->inventarisBaseQuery()
            ->selectRaw("COALESCE(l.lokasi,'-') as lokasi_name, COUNT(i.kode_label) as c")
            ->groupBy('lokasi_name')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['lokasi' => (string) $r->lokasi_name, 'count' => (int) $r->c])
            ->toArray();
    }

    public function render()
    {
        $metrics = $this->buildMetrics();
        $charts = $this->buildCharts();
        $topJenis = $this->buildTopJenis();
        $topLokasi = $this->buildTopLokasi();

        return view('livewire.bod.inventaris-dashboard', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->holdingOptions(),
            'lokasiOptions' => $this->lokasiOptions(),
            'ruanganOptions' => $this->ruanganOptions(),
            'jenisOptions' => $this->jenisOptions(),
            'statusOptions' => $this->statusOptions(),
            'lifecycleOptions' => $this->lifecycleOptions(),
            'metrics' => $metrics,
            'charts' => $charts,
            'topJenis' => $topJenis,
            'topLokasi' => $topLokasi,
        ])->layout('components.sccr-layout');
    }
}
