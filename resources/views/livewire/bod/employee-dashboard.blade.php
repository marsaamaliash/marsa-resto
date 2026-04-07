{{-- resources/views/livewire/bod/employee-dashboard.blade.php --}}
<x-ui.sccr-card transparent class="h-full min-h-0 flex flex-col">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">BoD · Employee Dashboard</h1>
                <p class="text-slate-200 text-sm">Ringkasan headcount, tren, dan distribusi data karyawan</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Updated <span class="font-bold text-yellow-300">{{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="px-4 pt-4 pb-2">
        <div class="rounded-2xl bg-white shadow border p-4">

            {{-- row 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding</span>
                    <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="['' => 'Semua'] + $holdingOptions"
                        class="w-full" />
                </div>

                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Department</span>
                    <x-ui.sccr-select name="filterDepartment" wire:model.live="filterDepartment" :options="['' => 'Semua'] + $departmentOptions"
                        class="w-full" />
                </div>

                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Division</span>
                    <x-ui.sccr-select name="filterDivision" wire:model.live="filterDivision" :options="['' => 'Semua'] + $divisionOptions"
                        class="w-full" />
                </div>

                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select name="filterStatus" wire:model.live="filterStatus" :options="[
                        '' => 'Semua',
                        'active' => 'Active (non-RESIGN)',
                        'resign' => 'RESIGN',
                    ]"
                        class="w-full" />
                </div>
            </div>

            {{-- row 2: period + buttons (right aligned) --}}
            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-end gap-2">
                <div class="flex gap-2">
                    <div class="relative">
                        <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">From</span>
                        <input id="bod-emp-from" type="text" inputmode="none" autocomplete="off" readonly
                            wire:model.defer="fromMonth"
                            class="w-40 rounded-lg border-gray-300 text-sm px-3 py-2 bg-white" />
                    </div>

                    <div class="relative">
                        <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">To</span>
                        <input id="bod-emp-to" type="text" inputmode="none" autocomplete="off" readonly
                            wire:model.defer="toMonth"
                            class="w-40 rounded-lg border-gray-300 text-sm px-3 py-2 bg-white" />
                    </div>
                </div>

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
                Periode dipilih via picker (tanpa salah ketik). Default: bulan berjalan (1 bulan ini s/d sekarang).
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="px-4 pb-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Total Employees</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['total'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Active</div>
                <div class="text-3xl font-extrabold text-emerald-700 mt-1">{{ (int) ($metrics['active'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">RESIGN</div>
                <div class="text-3xl font-extrabold text-rose-700 mt-1">{{ (int) ($metrics['resign'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">New Hires (This Month)</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['hire_this_month'] ?? 0) }}
                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Resign (This Month)</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">
                    {{ (int) ($metrics['resign_this_month'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- CHARTS + TABLE --}}
    <div class="flex-1 min-h-0 px-4 pb-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 h-full min-h-0">

            {{-- Hires vs Resign --}}
            <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-2 flex flex-col min-h-0">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-extrabold text-gray-800">Hires vs Resign</div>
                        <div class="text-[11px] text-gray-500">Per bulan (best-effort)</div>
                    </div>
                </div>

                <div class="flex-1 min-h-0 mt-3" wire:ignore>
                    <canvas id="bod-emp-chart-hires" height="120"></canvas>
                </div>
            </div>

            {{-- Status Pie --}}
            <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-0">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Status Distribution</div>
                    <div class="text-[11px] text-gray-500">employee_status</div>
                </div>

                <div class="flex-1 min-h-0 mt-3" wire:ignore>
                    <canvas id="bod-emp-chart-status" height="160"></canvas>
                </div>
            </div>

            {{-- By Holding --}}
            <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-2 flex flex-col min-h-0">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Headcount by Holding</div>
                    <div class="text-[11px] text-gray-500">Top 8</div>
                </div>

                <div class="flex-1 min-h-0 mt-3" wire:ignore>
                    <canvas id="bod-emp-chart-holding" height="120"></canvas>
                </div>
            </div>

            {{-- Top Departments --}}
            <div class="bg-white border rounded-2xl shadow p-4 flex flex-col min-h-0">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Top Departments</div>
                    <div class="text-[11px] text-gray-500">Top 10</div>
                </div>

                <div class="mt-3 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="text-left text-[11px] uppercase text-gray-500">
                                <th class="py-2 pr-2">Department</th>
                                <th class="py-2 text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($topDepts as $r)
                                <tr>
                                    <td class="py-2 pr-2 text-gray-800">{{ $r['department'] }}</td>
                                    <td class="py-2 text-right font-bold text-slate-900">{{ (int) $r['count'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-6 text-center text-gray-400 italic">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart data (PASTI ke-update oleh Livewire) --}}
    <textarea id="bod-emp-chart-data" class="hidden">
{!! json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</textarea>

    {{-- Toast --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
        wire:key="bod-emp-toast-{{ microtime() }}" />

    @once
        {{-- Flatpickr month picker --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

        {{-- Chart.js --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        <script>
            (function() {
                let hiresChart = null;
                let holdingChart = null;
                let statusChart = null;

                function readData() {
                    const el = document.getElementById('bod-emp-chart-data');
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

                    const ctxH = document.getElementById('bod-emp-chart-hires');
                    if (ctxH && !hiresChart) {
                        hiresChart = new Chart(ctxH, {
                            type: 'line',
                            data: {
                                labels: data.months || [],
                                datasets: [{
                                        label: 'Hires',
                                        data: data.hires || [],
                                        tension: 0.25
                                    },
                                    {
                                        label: 'Resign',
                                        data: data.resigns || [],
                                        tension: 0.25
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

                    const ctxB = document.getElementById('bod-emp-chart-holding');
                    if (ctxB && !holdingChart) {
                        holdingChart = new Chart(ctxB, {
                            type: 'bar',
                            data: {
                                labels: (data.by_holding || []).map(x => x.label),
                                datasets: [{
                                    label: 'Employees',
                                    data: (data.by_holding || []).map(x => x.count)
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
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

                    const ctxP = document.getElementById('bod-emp-chart-status');
                    if (ctxP && !statusChart) {
                        statusChart = new Chart(ctxP, {
                            type: 'pie',
                            data: {
                                labels: (data.status || []).map(x => x.label),
                                datasets: [{
                                    data: (data.status || []).map(x => x.count)
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
                }

                function updateCharts() {
                    const data = readData();
                    if (!data) return;

                    if (hiresChart) {
                        hiresChart.data.labels = data.months || [];
                        hiresChart.data.datasets[0].data = data.hires || [];
                        hiresChart.data.datasets[1].data = data.resigns || [];
                        hiresChart.update();
                    }

                    if (holdingChart) {
                        holdingChart.data.labels = (data.by_holding || []).map(x => x.label);
                        holdingChart.data.datasets[0].data = (data.by_holding || []).map(x => x.count);
                        holdingChart.update();
                    }

                    if (statusChart) {
                        statusChart.data.labels = (data.status || []).map(x => x.label);
                        statusChart.data.datasets[0].data = (data.status || []).map(x => x.count);
                        statusChart.update();
                    }
                }

                function ensureMonthPickers() {
                    const fromEl = document.getElementById('bod-emp-from');
                    const toEl = document.getElementById('bod-emp-to');
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

                // backend forces refresh when click Terapkan/Reset
                window.addEventListener('bod-emp-charts-refresh', boot);

                // Livewire v3 event
                if (window.Livewire && typeof Livewire.on === 'function') {
                    Livewire.on('bod-emp-charts-refresh', boot);
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
