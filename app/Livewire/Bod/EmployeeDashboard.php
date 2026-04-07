<?php

namespace App\Livewire\Bod;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class EmployeeDashboard extends Component
{
    private const MODULE_CODE_BOD_SDM = '00002';

    /** UI */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /** Caps */
    public bool $canView = false;

    /** Filters */
    public string $filterHolding = '';

    public string $filterDepartment = '';

    public string $filterDivision = '';

    public string $filterStatus = ''; // ''|active|resign

    /** Range (YYYY-MM) */
    public string $fromMonth = '';

    public string $toMonth = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'BoD', 'route' => 'bod.employees.dashboard', 'color' => 'text-white'],
            ['label' => 'Employee Dashboard', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();

        if (! $this->canView) {
            abort(403, 'Forbidden');
        }

        // default: bulan berjalan (1 bulan ini s/d now())
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
            || ($u?->hasPermission('BOD_EMP_DASH_VIEW') ?? false)
            || ($u?->hasPermission('EMP_VIEW') ?? false)
            || $this->userHasModuleAccess($u, self::MODULE_CODE_BOD_SDM)
        );
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
            $this->filterDepartment = '';
            $this->filterDivision = '';
        }

        if ($property === 'filterDepartment') {
            $this->filterDivision = '';
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
        // default: bulan berjalan
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
            $this->dispatchBrowserEvent('bod-emp-charts-refresh');

            return;
        }

        // Livewire v3
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('bod-emp-charts-refresh');
        }
    }

    /* =========================
     * OPTIONS
     * ========================= */
    protected function holdingOptions(): array
    {
        return DB::table('holdings')
            ->orderBy('name')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [
                (string) $h->id => trim(((string) $h->alias.' - '.(string) $h->name), ' -'),
            ])
            ->toArray();
    }

    protected function departmentOptions(): array
    {
        $q = DB::table('departments')->orderBy('name');

        if ($this->filterHolding !== '' && Schema::hasColumn('departments', 'holding_id')) {
            $q->where('holding_id', (int) $this->filterHolding);
        }

        return $q->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [(string) $d->id => (string) $d->name])
            ->toArray();
    }

    protected function divisionOptions(): array
    {
        $q = DB::table('divisions')->orderBy('name');

        if ($this->filterDepartment !== '' && Schema::hasColumn('divisions', 'department_id')) {
            $q->where('department_id', (int) $this->filterDepartment);
        }

        return $q->get(['id', 'name'])
            ->mapWithKeys(fn ($dv) => [(string) $dv->id => (string) $dv->name])
            ->toArray();
    }

    /* =========================
     * DATE/RANGE HELPERS
     * ========================= */
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

    protected function joinDateColumn(): ?string
    {
        if (Schema::hasColumn('employees', 'tanggal_join')) {
            return 'e.tanggal_join';
        }
        if (Schema::hasColumn('employees', 'created_at')) {
            return 'e.created_at';
        }

        return null;
    }

    protected function resignDateColumn(): ?string
    {
        if (Schema::hasColumn('employees', 'status_changed_at')) {
            return 'e.status_changed_at';
        }
        if (Schema::hasColumn('employees', 'updated_at')) {
            return 'e.updated_at';
        }

        return null;
    }

    /**
     * Normalize from/to as YYYY-MM and ensure from <= to.
     */
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

    /**
     * Bounds untuk filter join date:
     * - dari awal bulan fromMonth
     * - sampai akhir bulan toMonth, TAPI kalau toMonth == bulan sekarang => sampai now()
     */
    protected function rangeBounds(): array
    {
        [$fromYm, $toYm] = $this->rangeYm();

        $now = Carbon::now();
        $from = Carbon::createFromFormat('Y-m', $fromYm)->startOfMonth()->startOfDay();

        $toIsCurrentMonth = ($toYm === $now->format('Y-m'));
        $to = $toIsCurrentMonth
            ? $now
            : Carbon::createFromFormat('Y-m', $toYm)->endOfMonth()->endOfDay();

        return [$fromYm, $toYm, $from, $to];
    }

    protected function monthBounds(string $ym): array
    {
        $now = Carbon::now();
        $start = Carbon::createFromFormat('Y-m', $ym)->startOfMonth()->startOfDay();

        $end = ($ym === $now->format('Y-m'))
            ? $now
            : Carbon::createFromFormat('Y-m', $ym)->endOfMonth()->endOfDay();

        return [$start, $end];
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
     * BASE QUERY
     * ========================= */
    protected function employeesBaseQuery()
    {
        $q = DB::table('employees as e')
            ->leftJoin('holdings as h', 'h.id', '=', 'e.holding_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->leftJoin('divisions as dv', 'dv.id', '=', 'e.division_id');

        if (Schema::hasColumn('employees', 'is_deleted')) {
            $q->where('e.is_deleted', 0);
        }
        if (Schema::hasColumn('employees', 'deleted_at')) {
            $q->whereNull('e.deleted_at');
        }

        if ($this->filterHolding !== '') {
            $q->where('e.holding_id', (int) $this->filterHolding);
        }
        if ($this->filterDepartment !== '') {
            $q->where('e.department_id', (int) $this->filterDepartment);
        }
        if ($this->filterDivision !== '') {
            $q->where('e.division_id', (int) $this->filterDivision);
        }

        // RANGE FILTER (JOIN date)
        $joinCol = $this->joinDateColumn();
        if ($joinCol) {
            [$_fromYm, $_toYm, $fromBound, $toBound] = $this->rangeBounds();
            $q->whereNotNull($joinCol)
                ->whereBetween($joinCol, [$fromBound, $toBound]);
        }

        if ($this->filterStatus === 'resign') {
            $q->where('e.employee_status', 'RESIGN');
        } elseif ($this->filterStatus === 'active') {
            $q->where(function ($w) {
                $w->whereNull('e.employee_status')
                    ->orWhere('e.employee_status', '<>', 'RESIGN');
            });
        }

        return $q;
    }

    /* =========================
     * METRICS
     * ========================= */
    protected function buildMetrics(): array
    {
        $base = $this->employeesBaseQuery();

        $total = (clone $base)->count('e.nip');

        $active = (clone $base)
            ->where(function ($w) {
                $w->whereNull('e.employee_status')
                    ->orWhere('e.employee_status', '<>', 'RESIGN');
            })
            ->count('e.nip');

        $resign = (clone $base)
            ->where('e.employee_status', 'RESIGN')
            ->count('e.nip');

        // "This Month" = bulan akhir range (toMonth)
        [$_fromYm, $toYm] = $this->rangeYm();
        [$monthStart, $monthEnd] = $this->monthBounds($toYm);

        $hireThisMonth = 0;
        $hireCol = $this->joinDateColumn();
        if ($hireCol) {
            $hireThisMonth = (clone $base)
                ->whereNotNull($hireCol)
                ->whereBetween($hireCol, [$monthStart, $monthEnd])
                ->count('e.nip');
        }

        $resignThisMonth = 0;
        $resignCol = $this->resignDateColumn();
        if ($resignCol) {
            $resignThisMonth = (clone $base)
                ->where('e.employee_status', 'RESIGN')
                ->whereNotNull($resignCol)
                ->whereBetween($resignCol, [$monthStart, $monthEnd])
                ->count('e.nip');
        }

        return [
            'total' => $total,
            'active' => $active,
            'resign' => $resign,
            'hire_this_month' => $hireThisMonth,
            'resign_this_month' => $resignThisMonth,
        ];
    }

    /* =========================
     * CHARTS
     * ========================= */
    protected function buildCharts(): array
    {
        $months = $this->monthRange();

        $hires = array_fill_keys($months, 0);
        $hireCol = $this->joinDateColumn();

        if ($hireCol) {
            $expr = $this->monthExpr($hireCol);

            $rows = $this->employeesBaseQuery()
                ->selectRaw("$expr as ym, COUNT(e.nip) as c")
                ->whereNotNull($hireCol)
                ->whereRaw("$expr >= ? AND $expr <= ?", [$months[0], $months[count($months) - 1]])
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->get();

            foreach ($rows as $r) {
                $ym = (string) $r->ym;
                if (isset($hires[$ym])) {
                    $hires[$ym] = (int) $r->c;
                }
            }
        }

        $resigns = array_fill_keys($months, 0);
        $resignCol = $this->resignDateColumn();

        if ($resignCol) {
            $expr = $this->monthExpr($resignCol);

            $rows = $this->employeesBaseQuery()
                ->selectRaw("$expr as ym, COUNT(e.nip) as c")
                ->where('e.employee_status', 'RESIGN')
                ->whereNotNull($resignCol)
                ->whereRaw("$expr >= ? AND $expr <= ?", [$months[0], $months[count($months) - 1]])
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->get();

            foreach ($rows as $r) {
                $ym = (string) $r->ym;
                if (isset($resigns[$ym])) {
                    $resigns[$ym] = (int) $r->c;
                }
            }
        }

        $byHolding = $this->employeesBaseQuery()
            ->selectRaw("COALESCE(h.alias,'-') as holding_alias, COALESCE(h.name,'-') as holding_name, COUNT(e.nip) as c")
            ->groupBy('holding_alias', 'holding_name')
            ->orderByDesc('c')
            ->limit(8)
            ->get()
            ->map(fn ($r) => [
                'label' => trim(((string) $r->holding_alias).' - '.((string) $r->holding_name)),
                'count' => (int) $r->c,
            ])
            ->toArray();

        $statusRows = $this->employeesBaseQuery()
            ->selectRaw("COALESCE(e.employee_status,'(EMPTY)') as st, COUNT(e.nip) as c")
            ->groupBy('st')
            ->orderByDesc('c')
            ->get();

        $status = [];
        foreach ($statusRows as $r) {
            $label = (string) $r->st;
            if ($label === '(EMPTY)') {
                $label = 'ACTIVE/UNKNOWN';
            }
            $status[] = ['label' => $label, 'count' => (int) $r->c];
        }

        return [
            'months' => $months,
            'hires' => array_values($hires),
            'resigns' => array_values($resigns),
            'by_holding' => $byHolding,
            'status' => $status,
        ];
    }

    protected function buildTopDepartments(): array
    {
        return $this->employeesBaseQuery()
            ->selectRaw("COALESCE(d.name,'-') as department_name, COUNT(e.nip) as c")
            ->groupBy('department_name')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'department' => (string) $r->department_name,
                'count' => (int) $r->c,
            ])
            ->toArray();
    }

    public function render()
    {
        $metrics = $this->buildMetrics();
        $charts = $this->buildCharts();
        $topDepts = $this->buildTopDepartments();

        return view('livewire.bod.employee-dashboard', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->holdingOptions(),
            'departmentOptions' => $this->departmentOptions(),
            'divisionOptions' => $this->divisionOptions(),
            'metrics' => $metrics,
            'charts' => $charts,
            'topDepts' => $topDepts,
        ])->layout('components.sccr-layout');
    }
}
