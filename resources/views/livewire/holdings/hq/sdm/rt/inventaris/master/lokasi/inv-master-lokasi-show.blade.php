<x-ui.sccr-card class="max-w-4xl mx-auto p-4">
    @if ($row)
        @php
            $kode = $row->holding_kode . '.' . $row->lokasi_kode;
        @endphp

        <div class="space-y-4">

            {{-- HEADER --}}
            <div class="bg-slate-900 rounded-xl p-4 shadow-lg border-l-4 border-green-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-map-marker-alt fa-6x text-white"></i>
                </div>

                <span class="text-[10px] font-bold text-green-400 uppercase tracking-[0.2em]">
                    Master Lokasi • Detail
                </span>

                <h2 class="text-3xl font-mono font-extrabold text-white mt-1">
                    {{ $kode }}
                </h2>

                <div class="flex justify-between items-center text-xs mt-2">
                    <div class="text-gray-300 italic">
                        Holding: <span class="text-white font-semibold">{{ $row->nama_holding }}</span>
                    </div>

                    <div class="text-gray-300 italic">
                        Lokasi: <span class="text-white font-semibold">{{ $row->nama_lokasi }}</span>
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- LEFT: Summary --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="bg-gray-50 px-4 py-3 border-b text-sm font-bold text-gray-700 uppercase tracking-wider">
                            Ringkasan
                        </div>

                        <div class="p-4 space-y-3 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kode Holding
                                </p>
                                <p class="font-semibold text-gray-800">{{ $row->holding_kode }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Holding
                                </p>
                                <p class="font-semibold text-gray-800">{{ $row->nama_holding }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kode Lokasi</p>
                                <p class="font-mono font-bold text-green-700">{{ $row->lokasi_kode }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Lokasi</p>
                                <p class="font-semibold text-gray-800">{{ $row->nama_lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Info --}}
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="bg-gray-50 px-4 py-3 border-b text-sm font-bold text-gray-700 uppercase tracking-wider">
                            Informasi
                        </div>

                        <div class="p-4 text-sm space-y-3">
                            <div class="p-3 rounded-lg bg-blue-50 border border-blue-100 text-blue-900 text-xs">
                                <div class="font-semibold mb-1">Catatan</div>
                                <ul class="list-disc ml-5 space-y-1">
                                    <li>Data ini adalah <b>Truth Master</b> (dipakai dropdown transaksi Inventaris).
                                    </li>
                                    <li>Perubahan master akan berdampak ke pilihan lokasi di transaksi.</li>
                                    <li>Delete dilakukan via <b>request delete + approval</b>.</li>
                                </ul>
                            </div>

                            {{-- Kalau nanti ada kolom tambahan di tabel inv_lokasi (mis. created_at/updated_at/flags),
                                 bisa ditambahkan di sini. --}}
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <x-ui.sccr-button variant="secondary" wire:click="$dispatch('inv-master-lokasi-overlay-close')">
                    Kembali
                </x-ui.sccr-button>

                @if (auth()->user()?->hasPermission('INV_MASTER_LOKASI_UPDATE'))
                    <x-ui.sccr-button variant="warning"
                        wire:click="$dispatch('inv-master-lokasi-open-edit', { rowKey: '{{ $kode }}' })">
                        Edit Data
                    </x-ui.sccr-button>
                @endif
            </div>

        </div>
    @endif
</x-ui.sccr-card>
