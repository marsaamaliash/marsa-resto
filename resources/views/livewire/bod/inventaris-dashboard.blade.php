{{-- resources/views/livewire/bod/inventaris-dashboard.blade.php --}}
<x-ui.sccr-card transparent class="h-full min-h-0 flex flex-col">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">BoD · Inventaris Dashboard</h1>
                <p class="text-slate-200 text-sm">Ringkasan aset/inventaris, tren penambahan, perubahan status, dan
                    distribusi</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Updated <span class="font-bold text-yellow-300">{{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- FILTERS (collapsible + internal scroll) --}}
    <div class="px-4 pt-4 pb-2">
        <details open class="rounded-2xl bg-white shadow border">
            <summary class="px-4 py-3 cursor-pointer select-none flex items-center justify-between">
                <div class="font-extrabold text-slate-800 text-sm">Filters</div>
                <div class="text-[11px] text-slate-500">klik untuk buka/tutup</div>
            </summary>

            <div class="px-4 pb-4">
                {{-- area scroll untuk banyak filter --}}
                <div class="max-h-[260px] overflow-y-auto pr-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 min-w-0">

                        <div class="relative min-w-0">
                            <span
                                class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding</span>
                            <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="['' => 'Semua'] + $holdingOptions"
                                class="w-full min-w-0" />
                        </div>

                        <div class="relative min-w-0">
                            <span
                                class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Lokasi</span>
                            <x-ui.sccr-select name="filterLokasi" wire:model.live="filterLokasi" :options="['' => 'Semua'] + $lokasiOptions"
                                class="w-full min-w-0" />
                        </div>

                        <div class="relative min-w-0">
                            <span
                                class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Ruangan</span>
                            <x-ui.sccr-select name="filterRuangan" wire:model.live="filterRuangan" :options="['' => 'Semua'] + $ruanganOptions"
                                class="w-full min-w-0" />
                        </div>

                        <div class="relative min-w-0">
                            <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Jenis</span>
                            <x-ui.sccr-select name="filterJenis" wire:model.live="filterJenis" :options="['' => 'Semua'] + $jenisOptions"
                                class="w-full min-w-0" />
                        </div>

                        <div class="relative min-w-0">
                            <span
                                class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                            <x-ui.sccr-select name="filterStatus" wire:model.live="filterStatus" :options="$statusOptions"
                                class="w-full min-w-0" />
                        </div>

                        <div class="relative min-w-0">
                            <span
                                class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Lifecycle</span>
                            <x-ui.sccr-select name="filterLifecycle" wire:model.live="filterLifecycle" :options="$lifecycleOptions"
                                class="w-full min-w-0" />
                        </div>

                    </div>
                </div>

                {{-- row periode + tombol (rapi kanan) --}}
                <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-end gap-2">
                    <div class="flex gap-2">
                        <div class="relative">
                            <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">From</span>
                            <input id="bod-inv-from" type="text" inputmode="none" autocomplete="off" readonly
                                wire:model.defer="fromMonth"
                                class="w-40 rounded-lg border-gray-300 text-sm px-3 py-2 bg-white" />
                        </div>

                        <div class="relative">
                            <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">To</span>
                            <input id="bod-inv-to" type="text" inputmode="none" autocomplete="off" readonly
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
                    Periode dipilih via picker (tanpa salah ketik). Default: bulan berjalan (awal bulan ini s/d
                    sekarang).
                </div>
            </div>
        </details>
    </div>

    {{-- KPI CARDS --}}
    <div class="px-4 pb-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Total</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['total'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Baik</div>
                <div class="text-3xl font-extrabold text-emerald-700 mt-1">{{ (int) ($metrics['baik'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Rusak / Perbaikan</div>
                <div class="text-3xl font-extrabold text-amber-700 mt-1">{{ (int) ($metrics['rusak'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Hilang</div>
                <div class="text-3xl font-extrabold text-rose-700 mt-1">{{ (int) ($metrics['hilang'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Inactive</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['inactive'] ?? 0) }}</div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Pending Delete</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['pending_delete'] ?? 0) }}
                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Added (This Month)</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ (int) ($metrics['added_this_month'] ?? 0) }}
                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow p-4">
                <div class="text-[11px] uppercase font-bold text-gray-500">Status Updated (This Month)</div>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">
                    {{ (int) ($metrics['status_updated_this_month'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- CHARTS + TABLE --}}
    <div class="px-4 pb-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

            {{-- Trend --}}
            <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-2">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Added vs Status Updates</div>
                    <div class="text-[11px] text-gray-500">Per bulan</div>
                </div>
                <div class="mt-3 h-[280px]" wire:ignore>
                    <canvas id="bod-inv-chart-trend"></canvas>
                </div>
            </div>

            {{-- Status --}}
            <div class="bg-white border rounded-2xl shadow p-4">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Status Distribution</div>
                    <div class="text-[11px] text-gray-500">inventaris.status</div>
                </div>
                <div class="mt-3 h-[280px]" wire:ignore>
                    <canvas id="bod-inv-chart-status"></canvas>
                </div>
            </div>

            {{-- By Holding --}}
            <div class="bg-white border rounded-2xl shadow p-4 lg:col-span-2">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Inventaris by Holding</div>
                    <div class="text-[11px] text-gray-500">Top 8</div>
                </div>
                <div class="mt-3 h-[280px]" wire:ignore>
                    <canvas id="bod-inv-chart-holding"></canvas>
                </div>
            </div>

            {{-- Lifecycle --}}
            <div class="bg-white border rounded-2xl shadow p-4">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Lifecycle Distribution</div>
                    <div class="text-[11px] text-gray-500">inventaris.lifecycle_status</div>
                </div>
                <div class="mt-3 h-[280px]" wire:ignore>
                    <canvas id="bod-inv-chart-lifecycle"></canvas>
                </div>
            </div>

            {{-- Top Jenis --}}
            <div class="bg-white border rounded-2xl shadow p-4">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Top Jenis</div>
                    <div class="text-[11px] text-gray-500">Top 10</div>
                </div>
                <div class="mt-3 max-h-[280px] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="text-left text-[11px] uppercase text-gray-500">
                                <th class="py-2 pr-2">Jenis</th>
                                <th class="py-2 text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($topJenis as $r)
                                <tr>
                                    <td class="py-2 pr-2 text-gray-800">{{ $r['jenis'] }}</td>
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

            {{-- Top Lokasi --}}
            <div class="bg-white border rounded-2xl shadow p-4">
                <div>
                    <div class="text-sm font-extrabold text-gray-800">Top Lokasi</div>
                    <div class="text-[11px] text-gray-500">Top 10</div>
                </div>
                <div class="mt-3 max-h-[280px] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="text-left text-[11px] uppercase text-gray-500">
                                <th class="py-2 pr-2">Lokasi</th>
                                <th class="py-2 text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($topLokasi as $r)
                                <tr>
                                    <td class="py-2 pr-2 text-gray-800">{{ $r['lokasi'] }}</td>
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

    {{-- Chart data --}}
    <textarea id="bod-inv-chart-data" class="hidden">
{!! json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</textarea>

    {{-- Toast --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
        wire:key="bod-inv-toast-{{ microtime() }}" />

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
                let trendChart = null;
                let holdingChart = null;
                let statusChart = null;
                let lifecycleChart = null;

                function readData() {
                    const el = document.getElementById('bod-inv-chart-data');
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

                    const ctxT = document.getElementById('bod-inv-chart-trend');
                    if (ctxT && !trendChart) {
                        trendChart = new Chart(ctxT, {
                            type: 'line',
                            data: {
                                labels: data.months || [],
                                datasets: [{
                                        label: 'Added',
                                        data: data.added || [],
                                        tension: 0.25
                                    },
                                    {
                                        label: 'Status Updates',
                                        data: data.status_updates || [],
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

                    const ctxH = document.getElementById('bod-inv-chart-holding');
                    if (ctxH && !holdingChart) {
                        holdingChart = new Chart(ctxH, {
                            type: 'bar',
                            data: {
                                labels: (data.by_holding || []).map(x => x.label),
                                datasets: [{
                                    label: 'Inventaris',
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

                    const ctxS = document.getElementById('bod-inv-chart-status');
                    if (ctxS && !statusChart) {
                        statusChart = new Chart(ctxS, {
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

                    const ctxL = document.getElementById('bod-inv-chart-lifecycle');
                    if (ctxL && !lifecycleChart) {
                        lifecycleChart = new Chart(ctxL, {
                            type: 'doughnut',
                            data: {
                                labels: (data.lifecycle || []).map(x => x.label),
                                datasets: [{
                                    data: (data.lifecycle || []).map(x => x.count)
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

                    if (trendChart) {
                        trendChart.data.labels = data.months || [];
                        trendChart.data.datasets[0].data = data.added || [];
                        trendChart.data.datasets[1].data = data.status_updates || [];
                        trendChart.update();
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

                    if (lifecycleChart) {
                        lifecycleChart.data.labels = (data.lifecycle || []).map(x => x.label);
                        lifecycleChart.data.datasets[0].data = (data.lifecycle || []).map(x => x.count);
                        lifecycleChart.update();
                    }
                }

                function ensureMonthPickers() {
                    const fromEl = document.getElementById('bod-inv-from');
                    const toEl = document.getElementById('bod-inv-to');
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

                window.addEventListener('bod-inv-charts-refresh', boot);

                if (window.Livewire && typeof Livewire.on === 'function') {
                    Livewire.on('bod-inv-charts-refresh', boot);
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
