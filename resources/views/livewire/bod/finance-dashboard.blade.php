{{-- resources/views/livewire/bod/finance-dashboard.blade.php --}}
<x-ui.sccr-card transparent class="h-[calc(100vh-72px)] min-h-0 flex flex-col overflow-hidden">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden shrink-0">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">BoD · Finance Dashboard</h1>
                <p class="text-slate-200 text-sm">
                    Posisi keuangan, tren performa, dan ringkasan jurnal posted seluruh holding
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Updated <span class="font-bold text-yellow-300">{{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- SCROLLABLE BODY --}}
    <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden">
        <div class="px-4 pt-4 pb-16 space-y-3">

            {{-- FILTERS --}}
            <div class="rounded-2xl bg-white shadow border p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding</span>
                        <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="['' => 'Semua Holding'] + $holdingOptions"
                            class="w-full" />
                    </div>

                    <div class="relative">
                        <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">From</span>
                        <input id="bod-fin-from" type="text" inputmode="none" autocomplete="off" readonly
                            wire:model.defer="fromMonth"
                            class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 bg-white" />
                    </div>

                    <div class="relative">
                        <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">To</span>
                        <input id="bod-fin-to" type="text" inputmode="none" autocomplete="off" readonly
                            wire:model.defer="toMonth"
                            class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 bg-white" />
                    </div>
                </div>

                <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-end gap-2">
                    <div class="flex gap-2">
                        <button type="button" wire:click="applyRange" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow hover:bg-slate-900">
                            Terapkan
                        </button>

                        <button type="button" wire:click="resetRange" wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-[11px] text-gray-500">
                    Dashboard ini memakai jurnal berstatus <span class="font-semibold">posted</span>. Angka group saat
                    ini masih
                    <span class="font-semibold">gross sebelum eliminasi intercompany</span> kecuali jurnal eliminasi
                    sudah diposting.
                </div>
            </div>

            {{-- KPI CARDS --}}
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3">
                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Assets (as of end period)</div>
                        <div class="text-3xl font-extrabold text-emerald-700 mt-1">
                            {{ number_format((float) ($metrics['assets'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Liabilities (as of end period)</div>
                        <div class="text-3xl font-extrabold text-amber-700 mt-1">
                            {{ number_format((float) ($metrics['liabilities'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Equity (as of end period)</div>
                        <div class="text-3xl font-extrabold text-sky-700 mt-1">
                            {{ number_format((float) ($metrics['equity'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Revenue (period)</div>
                        <div class="text-3xl font-extrabold text-emerald-700 mt-1">
                            {{ number_format((float) ($metrics['revenue'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Expense (period)</div>
                        <div class="text-3xl font-extrabold text-rose-700 mt-1">
                            {{ number_format((float) ($metrics['expense'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl shadow p-4">
                        <div class="text-[11px] uppercase font-bold text-gray-500">Net Profit (period)</div>
                        <div
                            class="text-3xl font-extrabold mt-1 {{ ($metrics['net_profit'] ?? 0) >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                            {{ number_format((float) ($metrics['net_profit'] ?? 0), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-sm text-gray-600">
                    Posted Journals in Period:
                    <span class="font-bold text-slate-900">{{ (int) ($metrics['posted_journals'] ?? 0) }}</span>
                </div>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

                {{-- Revenue vs Expense vs Profit --}}
                <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-2 flex flex-col min-h-0">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Revenue vs Expense vs Profit</div>
                        <div class="text-[11px] text-gray-500">Per bulan dalam periode terpilih</div>
                    </div>

                    <div class="flex-1 min-h-0 mt-3" wire:ignore>
                        <canvas id="bod-fin-chart-performance" height="120"></canvas>
                    </div>
                </div>

                {{-- Composition --}}
                <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-0">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Position Composition</div>
                        <div class="text-[11px] text-gray-500">Assets vs Liabilities vs Equity</div>
                    </div>

                    <div class="flex-1 min-h-0 mt-3" wire:ignore>
                        <canvas id="bod-fin-chart-composition" height="160"></canvas>
                    </div>
                </div>

                {{-- By Holding --}}
                <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-3 flex flex-col">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Financial Position by Holding</div>
                        <div class="text-[11px] text-gray-500">As of end period</div>
                    </div>

                    <div class="mt-3 overflow-x-auto" wire:ignore>
                        <div class="min-w-[900px] h-[360px]">
                            <canvas id="bod-fin-chart-holding"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            {{-- TABLES --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-3">

                {{-- Top Revenue Accounts --}}
                <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-[320px]">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Top Revenue Accounts</div>
                        <div class="text-[11px] text-gray-500">Periode terpilih</div>
                    </div>

                    <div class="mt-3 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr class="text-left text-[11px] uppercase text-gray-500">
                                    <th class="py-2 pr-2">Code</th>
                                    <th class="py-2 pr-2">Account</th>
                                    <th class="py-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($topRevenueAccounts as $r)
                                    <tr>
                                        <td class="py-2 pr-2 font-semibold text-slate-900">{{ $r['natural_code'] }}</td>
                                        <td class="py-2 pr-2 text-gray-800">{{ $r['account_name'] }}</td>
                                        <td class="py-2 text-right font-bold text-emerald-700">
                                            {{ number_format((float) $r['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400 italic">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Top Expense Accounts --}}
                <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-[320px]">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Top Expense Accounts</div>
                        <div class="text-[11px] text-gray-500">Periode terpilih</div>
                    </div>

                    <div class="mt-3 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr class="text-left text-[11px] uppercase text-gray-500">
                                    <th class="py-2 pr-2">Code</th>
                                    <th class="py-2 pr-2">Account</th>
                                    <th class="py-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($topExpenseAccounts as $r)
                                    <tr>
                                        <td class="py-2 pr-2 font-semibold text-slate-900">{{ $r['natural_code'] }}
                                        </td>
                                        <td class="py-2 pr-2 text-gray-800">{{ $r['account_name'] }}</td>
                                        <td class="py-2 text-right font-bold text-rose-700">
                                            {{ number_format((float) $r['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400 italic">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Recent Journals --}}
                <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-[320px]">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Recent Posted Journals</div>
                        <div class="text-[11px] text-gray-500">Periode terpilih</div>
                    </div>

                    <div class="mt-3 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr class="text-left text-[11px] uppercase text-gray-500">
                                    <th class="py-2 pr-2">Date</th>
                                    <th class="py-2 pr-2">Holding</th>
                                    <th class="py-2 pr-2">Journal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($recentJournals as $r)
                                    <tr>
                                        <td class="py-2 pr-2 text-gray-700 whitespace-nowrap">{{ $r['journal_date'] }}
                                        </td>
                                        <td class="py-2 pr-2 text-gray-700">{{ $r['holding'] }}</td>
                                        <td class="py-2 pr-2">
                                            <div class="font-semibold text-slate-900">{{ $r['journal_no'] }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $r['description'] }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400 italic">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Chart data --}}
    <textarea id="bod-fin-chart-data" class="hidden">
{!! json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</textarea>

    {{-- Toast --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
        wire:key="bod-fin-toast-{{ microtime() }}" />

    @once
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        <script>
            (function() {
                let performanceChart = null;
                let compositionChart = null;
                let holdingChart = null;

                function readData() {
                    const el = document.getElementById('bod-fin-chart-data');
                    if (!el) return null;

                    const raw = (el.value || el.textContent || '').trim();
                    if (!raw) return null;

                    try {
                        return JSON.parse(raw);
                    } catch (e) {
                        return null;
                    }
                }

                function ensureCharts() {
                    const data = readData();
                    if (!data) return;

                    const perfCtx = document.getElementById('bod-fin-chart-performance');
                    if (perfCtx && !performanceChart) {
                        performanceChart = new Chart(perfCtx, {
                            type: 'line',
                            data: {
                                labels: data.months || [],
                                datasets: [{
                                        label: 'Revenue',
                                        data: data.revenue || [],
                                        tension: 0.25,
                                        borderColor: '#059669',
                                        backgroundColor: 'rgba(5, 150, 105, 0.10)'
                                    },
                                    {
                                        label: 'Expense',
                                        data: data.expense || [],
                                        tension: 0.25,
                                        borderColor: '#dc2626',
                                        backgroundColor: 'rgba(220, 38, 38, 0.10)'
                                    },
                                    {
                                        label: 'Profit',
                                        data: data.profit || [],
                                        tension: 0.25,
                                        borderColor: '#0f172a',
                                        backgroundColor: 'rgba(15, 23, 42, 0.10)'
                                    },
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }

                    const compCtx = document.getElementById('bod-fin-chart-composition');
                    if (compCtx && !compositionChart) {
                        compositionChart = new Chart(compCtx, {
                            type: 'doughnut',
                            data: {
                                labels: (data.composition || []).map(x => x.label),
                                datasets: [{
                                    data: (data.composition || []).map(x => x.amount),
                                    backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }

                    const holdCtx = document.getElementById('bod-fin-chart-holding');
                    if (holdCtx && !holdingChart) {
                        holdingChart = new Chart(holdCtx, {
                            type: 'bar',
                            data: {
                                labels: (data.by_holding || []).map(x => x.label),
                                datasets: [{
                                        label: 'Assets',
                                        data: (data.by_holding || []).map(x => x.assets),
                                        backgroundColor: '#10b981'
                                    },
                                    {
                                        label: 'Liabilities',
                                        data: (data.by_holding || []).map(x => x.liabilities),
                                        backgroundColor: '#f59e0b'
                                    },
                                    {
                                        label: 'Equity',
                                        data: (data.by_holding || []).map(x => x.equity),
                                        backgroundColor: '#0ea5e9'
                                    },
                                ]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                layout: {
                                    padding: {
                                        top: 8,
                                        right: 16,
                                        bottom: 8,
                                        left: 8
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true
                                    },
                                    y: {
                                        ticks: {
                                            autoSkip: false
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                function updateCharts() {
                    const data = readData();
                    if (!data) return;

                    if (performanceChart) {
                        performanceChart.data.labels = data.months || [];
                        performanceChart.data.datasets[0].data = data.revenue || [];
                        performanceChart.data.datasets[1].data = data.expense || [];
                        performanceChart.data.datasets[2].data = data.profit || [];
                        performanceChart.update();
                    }

                    if (compositionChart) {
                        compositionChart.data.labels = (data.composition || []).map(x => x.label);
                        compositionChart.data.datasets[0].data = (data.composition || []).map(x => x.amount);
                        compositionChart.update();
                    }

                    if (holdingChart) {
                        holdingChart.data.labels = (data.by_holding || []).map(x => x.label);
                        holdingChart.data.datasets[0].data = (data.by_holding || []).map(x => x.assets);
                        holdingChart.data.datasets[1].data = (data.by_holding || []).map(x => x.liabilities);
                        holdingChart.data.datasets[2].data = (data.by_holding || []).map(x => x.equity);
                        holdingChart.update();
                    }
                }

                function ensureMonthPickers() {
                    const fromEl = document.getElementById('bod-fin-from');
                    const toEl = document.getElementById('bod-fin-to');
                    if (!fromEl || !toEl || !window.flatpickr || !window.monthSelectPlugin) return;

                    const cfg = {
                        allowInput: false,
                        dateFormat: "Y-m",
                        plugins: [new monthSelectPlugin({
                            shorthand: true,
                            dateFormat: "Y-m",
                            altFormat: "F Y"
                        })],
                        onChange: function(selectedDates, dateStr, instance) {
                            instance.input.value = dateStr;
                            instance.input.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            instance.input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    };

                    if (!fromEl._flatpickr) flatpickr(fromEl, cfg);
                    if (!toEl._flatpickr) flatpickr(toEl, cfg);

                    try {
                        fromEl._flatpickr.setDate(fromEl.value || null, true, "Y-m");
                    } catch (e) {}
                    try {
                        toEl._flatpickr.setDate(toEl.value || null, true, "Y-m");
                    } catch (e) {}
                }

                function boot() {
                    ensureMonthPickers();
                    ensureCharts();
                    updateCharts();
                }

                window.addEventListener('bod-fin-charts-refresh', boot);

                if (window.Livewire && typeof Livewire.on === 'function') {
                    Livewire.on('bod-fin-charts-refresh', boot);
                }

                document.addEventListener('DOMContentLoaded', boot);
                document.addEventListener('livewire:load', boot);
                document.addEventListener('livewire:navigated', boot);

                if (window.Livewire && Livewire.hook) {
                    Livewire.hook('message.processed', boot);
                    Livewire.hook('morph.updated', boot);
                }
            })
            ();
        </script>
    @endonce

</x-ui.sccr-card>
