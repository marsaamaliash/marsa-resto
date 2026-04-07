<?php

namespace App\Livewire\Bod;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class FinanceDashboard extends Component
{
    private const MODULE_CODE_BOD_FINANCE = '00003';

    /** UI */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /** Caps */
    public bool $canView = false;

    /** Filters */
    public string $filterHolding = '';

    /** Range (YYYY-MM) */
    public string $fromMonth = '';

    public string $toMonth = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'BoD', 'route' => 'bod.finance.dashboard', 'color' => 'text-white'],
            ['label' => 'Finance Dashboard', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();

        if (! $this->canView) {
            abort(403, 'Forbidden');
        }

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
            || ($u?->hasPermission('BOD_FIN_DASH_VIEW') ?? false)
            || ($u?->hasPermission('FIN_VIEW') ?? false)
            || $this->userHasModuleAccess($u, self::MODULE_CODE_BOD_FINANCE)
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

    /* =========================
     * ACTIONS
     * ========================= */
    public function applyRange(): void
    {
        [$this->fromMonth, $this->toMonth] = $this->rangeYm();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Periode dashboard keuangan diterapkan.',
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
                'message' => 'Periode dashboard keuangan direset ke bulan berjalan.',
            ];
        }

        $this->dispatchChartsRefresh();
    }

    private function dispatchChartsRefresh(): void
    {
        if (method_exists($this, 'dispatchBrowserEvent')) {
            $this->dispatchBrowserEvent('bod-fin-charts-refresh');

            return;
        }

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('bod-fin-charts-refresh');
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
            ->mapWithKeys(function ($h) {
                $alias = trim((string) ($h->alias ?? ''));
                $name = trim((string) ($h->name ?? ''));

                $label = $alias !== '' && strcasecmp($alias, $name) !== 0
                    ? $alias.' - '.$name
                    : ($name !== '' ? $name : '-');

                return [(string) $h->id => $label];
            })
            ->toArray();
    }

    /* =========================
     * DATE HELPERS
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

    protected function journalDateColumn(): string
    {
        if (Schema::hasColumn('fin_journals', 'posting_date')) {
            return 'j.posting_date';
        }

        return 'j.journal_date';
    }

    protected function debitExpr(): string
    {
        if (Schema::hasColumn('fin_journal_lines', 'base_debit')) {
            return 'COALESCE(jl.base_debit, jl.debit)';
        }

        return 'jl.debit';
    }

    protected function creditExpr(): string
    {
        if (Schema::hasColumn('fin_journal_lines', 'base_credit')) {
            return 'COALESCE(jl.base_credit, jl.credit)';
        }

        return 'jl.credit';
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
     * QUERY BASE
     * ========================= */
    protected function postedLinesBaseQuery()
    {
        $q = DB::table('fin_journal_lines as jl')
            ->join('fin_journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('fin_accounts as fa', 'fa.id', '=', 'jl.account_id')
            ->leftJoin('holdings as h', 'h.id', '=', 'j.holding_id');

        if (Schema::hasColumn('fin_journals', 'deleted_at')) {
            $q->whereNull('j.deleted_at');
        }

        if (Schema::hasColumn('fin_accounts', 'deleted_at')) {
            $q->whereNull('fa.deleted_at');
        }

        if (Schema::hasColumn('fin_journals', 'status')) {
            $q->where('j.status', 'posted');
        }

        if ($this->filterHolding !== '') {
            $q->where('j.holding_id', (int) $this->filterHolding);
        }

        return $q;
    }

    protected function signedBalance(string $normalBalance, float $debit, float $credit): float
    {
        return strtolower($normalBalance) === 'debit'
            ? ($debit - $credit)
            : ($credit - $debit);
    }

    /* =========================
     * METRICS
     * ========================= */
    protected function buildMetrics(): array
    {
        [$fromYm, $toYm, $fromBound, $toBound] = $this->rangeBounds();
        $dateCol = $this->journalDateColumn();

        $positionRows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                fa.type as account_type,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->whereNotNull(DB::raw($dateCol))
            ->where($dateCol, '<=', $toBound)
            ->groupBy('fa.type', 'fa.normal_balance')
            ->get();

        $assets = 0.0;
        $liabilities = 0.0;
        $equity = 0.0;

        foreach ($positionRows as $r) {
            $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

            if ($r->account_type === 'Asset') {
                $assets += $bal;
            } elseif ($r->account_type === 'Liability') {
                $liabilities += $bal;
            } elseif ($r->account_type === 'Equity') {
                $equity += $bal;
            }
        }

        $flowRows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                fa.type as account_type,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->whereNotNull(DB::raw($dateCol))
            ->whereBetween($dateCol, [$fromBound, $toBound])
            ->whereIn('fa.type', ['Revenue', 'Expense'])
            ->groupBy('fa.type', 'fa.normal_balance')
            ->get();

        $revenue = 0.0;
        $expense = 0.0;

        foreach ($flowRows as $r) {
            $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

            if ($r->account_type === 'Revenue') {
                $revenue += $bal;
            } elseif ($r->account_type === 'Expense') {
                $expense += $bal;
            }
        }

        $netProfit = $revenue - $expense;

        $journalCount = (clone DB::table('fin_journals as j'))
            ->when(Schema::hasColumn('fin_journals', 'deleted_at'), fn ($q) => $q->whereNull('j.deleted_at'))
            ->when(Schema::hasColumn('fin_journals', 'status'), fn ($q) => $q->where('j.status', 'posted'))
            ->when($this->filterHolding !== '', fn ($q) => $q->where('j.holding_id', (int) $this->filterHolding))
            ->whereBetween($dateCol, [$fromBound, $toBound])
            ->count('j.id');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'revenue' => $revenue,
            'expense' => $expense,
            'net_profit' => $netProfit,
            'posted_journals' => $journalCount,
            'from_month' => $fromYm,
            'to_month' => $toYm,
        ];
    }

    /* =========================
     * CHARTS
     * ========================= */
    protected function buildCharts(): array
    {
        $months = $this->monthRange();
        [$fromYm, $toYm, $fromBound, $toBound] = $this->rangeBounds();
        $dateCol = $this->journalDateColumn();
        $monthExpr = $this->monthExpr($dateCol);

        $revenue = array_fill_keys($months, 0.0);
        $expense = array_fill_keys($months, 0.0);
        $profit = array_fill_keys($months, 0.0);

        $monthlyRows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                {$monthExpr} as ym,
                fa.type as account_type,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->whereBetween($dateCol, [$fromBound, $toBound])
            ->whereIn('fa.type', ['Revenue', 'Expense'])
            ->groupBy('ym', 'fa.type', 'fa.normal_balance')
            ->orderBy('ym', 'asc')
            ->get();

        foreach ($monthlyRows as $r) {
            $ym = (string) $r->ym;
            if (! isset($revenue[$ym])) {
                continue;
            }

            $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

            if ($r->account_type === 'Revenue') {
                $revenue[$ym] += $bal;
            } elseif ($r->account_type === 'Expense') {
                $expense[$ym] += $bal;
            }
        }

        foreach ($months as $ym) {
            $profit[$ym] = $revenue[$ym] - $expense[$ym];
        }

        $holdingRows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                COALESCE(h.alias,'') as holding_alias,
                COALESCE(h.name,'') as holding_name,
                fa.type as account_type,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->where($dateCol, '<=', $toBound)
            ->whereIn('fa.type', ['Asset', 'Liability', 'Equity'])
            ->groupBy('holding_alias', 'holding_name', 'fa.type', 'fa.normal_balance')
            ->get();

        $byHoldingMap = [];
        foreach ($holdingRows as $r) {
            $alias = trim((string) ($r->holding_alias ?? ''));
            $name = trim((string) ($r->holding_name ?? ''));

            if ($alias !== '' && strcasecmp($alias, $name) !== 0) {
                $key = $alias;
            } else {
                $key = $name !== '' ? $name : '-';
            }

            if (! isset($byHoldingMap[$key])) {
                $byHoldingMap[$key] = [
                    'label' => $key,
                    'assets' => 0.0,
                    'liabilities' => 0.0,
                    'equity' => 0.0,
                ];
            }

            $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

            if ($r->account_type === 'Asset') {
                $byHoldingMap[$key]['assets'] += $bal;
            } elseif ($r->account_type === 'Liability') {
                $byHoldingMap[$key]['liabilities'] += $bal;
            } elseif ($r->account_type === 'Equity') {
                $byHoldingMap[$key]['equity'] += $bal;
            }
        }

        $byHolding = collect($byHoldingMap)
            ->sortByDesc(fn ($r) => abs($r['assets']) + abs($r['liabilities']) + abs($r['equity']))
            ->take(12)
            ->values()
            ->all();

        $positionRows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                fa.type as account_type,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->where($dateCol, '<=', $toBound)
            ->whereIn('fa.type', ['Asset', 'Liability', 'Equity'])
            ->groupBy('fa.type', 'fa.normal_balance')
            ->get();

        $composition = [
            ['label' => 'Assets', 'amount' => 0.0],
            ['label' => 'Liabilities', 'amount' => 0.0],
            ['label' => 'Equity', 'amount' => 0.0],
        ];

        foreach ($positionRows as $r) {
            $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

            if ($r->account_type === 'Asset') {
                $composition[0]['amount'] += $bal;
            } elseif ($r->account_type === 'Liability') {
                $composition[1]['amount'] += $bal;
            } elseif ($r->account_type === 'Equity') {
                $composition[2]['amount'] += $bal;
            }
        }

        return [
            'months' => $months,
            'revenue' => array_values($revenue),
            'expense' => array_values($expense),
            'profit' => array_values($profit),
            'by_holding' => $byHolding,
            'composition' => $composition,
        ];
    }

    protected function buildTopAccounts(string $type): array
    {
        [$fromYm, $toYm, $fromBound, $toBound] = $this->rangeBounds();
        $dateCol = $this->journalDateColumn();

        $rows = (clone $this->postedLinesBaseQuery())
            ->selectRaw("
                fa.natural_code as natural_code,
                fa.name as account_name,
                fa.normal_balance as normal_balance,
                SUM({$this->debitExpr()}) as debit_sum,
                SUM({$this->creditExpr()}) as credit_sum
            ")
            ->whereBetween($dateCol, [$fromBound, $toBound])
            ->where('fa.type', $type)
            ->groupBy('fa.natural_code', 'fa.name', 'fa.normal_balance')
            ->get();

        return $rows
            ->map(function ($r) {
                $bal = $this->signedBalance((string) $r->normal_balance, (float) $r->debit_sum, (float) $r->credit_sum);

                return [
                    'natural_code' => (string) $r->natural_code,
                    'account_name' => (string) $r->account_name,
                    'amount' => $bal,
                ];
            })
            ->sortByDesc(fn ($r) => abs($r['amount']))
            ->take(10)
            ->values()
            ->all();
    }

    protected function buildRecentJournals(): array
    {
        [$fromYm, $toYm, $fromBound, $toBound] = $this->rangeBounds();
        $dateCol = $this->journalDateColumn();

        return DB::table('fin_journals as j')
            ->leftJoin('holdings as h', 'h.id', '=', 'j.holding_id')
            ->when(Schema::hasColumn('fin_journals', 'deleted_at'), fn ($q) => $q->whereNull('j.deleted_at'))
            ->when(Schema::hasColumn('fin_journals', 'status'), fn ($q) => $q->where('j.status', 'posted'))
            ->when($this->filterHolding !== '', fn ($q) => $q->where('j.holding_id', (int) $this->filterHolding))
            ->whereBetween($dateCol, [$fromBound, $toBound])
            ->orderBy($dateCol, 'desc')
            ->limit(12)
            ->get([
                'j.journal_no',
                'j.reference_no',
                'j.description',
                'j.holding_id',
                'j.journal_date',
                'h.alias as holding_alias',
                'h.name as holding_name',
            ])
            ->map(function ($r) {
                $alias = trim((string) ($r->holding_alias ?? ''));
                $name = trim((string) ($r->holding_name ?? ''));

                $holding = $alias !== '' && strcasecmp($alias, $name) !== 0
                    ? $alias.' - '.$name
                    : ($name !== '' ? $name : '-');

                return [
                    'journal_no' => (string) $r->journal_no,
                    'reference_no' => (string) ($r->reference_no ?? '-'),
                    'description' => (string) ($r->description ?? '-'),
                    'holding' => $holding,
                    'journal_date' => (string) ($r->journal_date ?? ''),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        $metrics = $this->buildMetrics();
        $charts = $this->buildCharts();
        $topRevenueAccounts = $this->buildTopAccounts('Revenue');
        $topExpenseAccounts = $this->buildTopAccounts('Expense');
        $recentJournals = $this->buildRecentJournals();

        return view('livewire.bod.finance-dashboard', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->holdingOptions(),
            'metrics' => $metrics,
            'charts' => $charts,
            'topRevenueAccounts' => $topRevenueAccounts,
            'topExpenseAccounts' => $topExpenseAccounts,
            'recentJournals' => $recentJournals,
        ])->layout('components.sccr-layout');
    }
}
