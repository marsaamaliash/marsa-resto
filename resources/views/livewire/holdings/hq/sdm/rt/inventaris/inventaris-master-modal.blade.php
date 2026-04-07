<div>
    @if ($show)
        <div class="fixed inset-0 z-50">
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Modal Wrapper --}}
            <div class="relative h-full w-full flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl overflow-hidden
                        max-h-[calc(100vh-2rem)] flex flex-col">

                    {{-- Header --}}
                    <div
                        class="relative px-8 py-5 bg-green-600/90 shadow overflow-hidden flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-bold text-white">
                                Master
                                {{ $type === 'lokasi' ? 'Lokasi' : ($type === 'ruangan' ? 'Ruangan' : 'Jenis Barang') }}
                            </h2>
                            <p class="text-green-100 text-xs md:text-sm">
                                Create-only. List di bawah read-only dari VIEW MySQL.
                            </p>
                        </div>

                        <x-ui.sccr-button type="button" variant="icon" wire:click="close"
                            class="text-white/80 hover:text-white hover:bg-white/10 border border-white/20 rounded-xl"
                            title="Tutup">
                            <span class="text-xl leading-none">×</span>
                        </x-ui.sccr-button>
                    </div>

                    {{-- Body (scroll container) --}}
                    <div class="p-6 flex-1 overflow-hidden">

                        {{-- toast --}}
                        @if ($toast['show'])
                            <div
                                class="mb-4 p-3 rounded-lg border text-sm
                            {{ $toast['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-900' : '' }}
                            {{ $toast['type'] === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-900' : '' }}
                            {{ $toast['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-900' : '' }}
                        ">
                                <b>{{ strtoupper($toast['type']) }}</b> — {{ $toast['message'] }}
                            </div>
                        @endif

                        {{-- Filters (top) --}}
                        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="relative">
                                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                                        Search
                                    </span>
                                    <x-ui.sccr-input wire:model.live.debounce.400ms="search"
                                        placeholder="Cari kode/nama..." class="w-64" />
                                </div>

                                @if ($type === 'lokasi' || $type === 'ruangan')
                                    <div class="relative">
                                        <span
                                            class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                                            Holding
                                        </span>
                                        <x-ui.sccr-select wire:model.live="holdingKode" :options="$this->holdingOptions"
                                            class="w-60" />
                                    </div>
                                @endif

                                @if ($type === 'ruangan')
                                    <div class="relative">
                                        <span
                                            class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                                            Lokasi
                                        </span>
                                        <x-ui.sccr-select wire:model.live="lokasiKode" :options="$this->lokasiOptions"
                                            class="w-60" />
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 2 Columns --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-18rem)] max-h-[520px]">
                            {{-- LEFT: LIST --}}
                            <div class="lg:col-span-7 flex flex-col min-h-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-bold text-gray-500 uppercase">Daftar Data</h3>
                                    {{-- <span class="text-[11px] text-gray-400">Max 200 baris</span> --}}
                                </div>

                                <div class="border rounded-xl overflow-hidden shadow-sm flex-1 min-h-0 bg-gray-50">
                                    {{-- table scroll --}}
                                    <div class="h-full overflow-y-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-700 sticky top-0 z-10">
                                                <tr>
                                                    @if ($type !== 'jenis')
                                                        <th
                                                            class="px-4 py-3 text-left text-[10px] font-bold uppercase text-white">
                                                            Holding
                                                        </th>
                                                    @endif

                                                    @if ($type === 'ruangan')
                                                        <th
                                                            class="px-4 py-3 text-left text-[10px] font-bold uppercase text-white">
                                                            Lokasi
                                                        </th>
                                                    @endif

                                                    <th
                                                        class="px-4 py-3 text-left text-[10px] font-bold uppercase text-white w-20">
                                                        Kode
                                                    </th>

                                                    <th
                                                        class="px-4 py-3 text-left text-[10px] font-bold uppercase text-white">
                                                        Nama
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-100 bg-white">
                                                @forelse($rows as $r)
                                                    <tr class="hover:bg-gray-300 transition">
                                                        @if ($type === 'lokasi')
                                                            <td class="px-4 py-2 text-xs">
                                                                <div class="font-semibold text-blue-700">
                                                                    {{ $r->holding_kode }}</div>
                                                                <div class="text-[10px] text-gray-500 uppercase italic">
                                                                    {{ $r->nama_holding }}</div>
                                                            </td>
                                                            <td
                                                                class="px-4 py-2 font-mono text-xs font-bold text-green-700">
                                                                {{ $r->lokasi_kode }}</td>
                                                            <td class="px-4 py-2 text-xs font-semibold text-gray-800">
                                                                {{ $r->nama_lokasi }}</td>
                                                        @elseif ($type === 'ruangan')
                                                            <td class="px-4 py-2 text-xs">
                                                                <div class="font-semibold text-blue-700">
                                                                    {{ $r->holding_kode }}</div>
                                                                <div class="text-[10px] text-gray-500 uppercase italic">
                                                                    {{ $r->nama_holding }}</div>
                                                            </td>
                                                            <td class="px-4 py-2 text-xs">
                                                                <div class="font-semibold text-green-700">
                                                                    {{ $r->lokasi_kode }}</div>
                                                                <div class="text-[10px] text-gray-500">
                                                                    {{ $r->nama_lokasi }}</div>
                                                            </td>
                                                            <td
                                                                class="px-4 py-2 font-mono text-xs font-bold text-green-700">
                                                                {{ $r->kode_ruangan }}</td>
                                                            <td class="px-4 py-2 text-xs font-semibold text-gray-800">
                                                                {{ $r->nama_ruangan }}</td>
                                                        @else
                                                            <td
                                                                class="px-4 py-2 font-mono text-xs font-bold text-blue-700">
                                                                {{ $r->jenis_kode }}</td>
                                                            <td class="px-4 py-2 text-xs font-semibold text-gray-800">
                                                                {{ $r->nama_jenis }}</td>
                                                        @endif
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4"
                                                            class="py-10 text-center text-gray-400 italic text-sm">
                                                            Data tidak ditemukan
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT: FORM --}}
                            <div class="lg:col-span-5 flex flex-col min-h-0">
                                <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Tambah Baru</h3>

                                <div class="bg-gray-50/70 border rounded-2xl p-5 flex-1 overflow-y-auto">
                                    <div class="grid grid-cols-1 gap-3">
                                        @if ($type === 'lokasi' || $type === 'ruangan')
                                            <div>
                                                <label class="text-xs font-bold text-gray-700">Holding</label>
                                                <x-ui.sccr-select wire:model.live="holdingKode" :options="$this->holdingOptions" />
                                                @error('holdingKode')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif

                                        @if ($type === 'ruangan')
                                            <div>
                                                <label class="text-xs font-bold text-gray-700">Lokasi</label>
                                                <x-ui.sccr-select wire:model.live="lokasiKode" :options="$this->lokasiOptions" />
                                                @error('lokasiKode')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-12 gap-3">
                                            <div class="col-span-4">
                                                <label class="text-xs font-bold text-gray-700">Kode</label>
                                                <x-ui.sccr-input wire:model.defer="kode" placeholder="01" />
                                                @error('kode')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-span-8">
                                                <label class="text-xs font-bold text-gray-700">Nama</label>
                                                <x-ui.sccr-input wire:model.defer="nama" placeholder="Nama..." />
                                                @error('nama')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="pt-3 flex flex-col gap-2">
                                            <x-ui.sccr-button type="button" variant="success" wire:click="save"
                                                class="w-full">
                                                🚀 Simpan Master
                                            </x-ui.sccr-button>

                                            <x-ui.sccr-button type="button" variant="secondary" wire:click="close"
                                                class="w-full">
                                                Keluar Master
                                            </x-ui.sccr-button>

                                            <div class="text-[11px] text-gray-500 mt-1">
                                                *Create-only. Tidak ada edit/delete. List kiri dari VIEW (read-only).
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- /2 columns --}}
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
