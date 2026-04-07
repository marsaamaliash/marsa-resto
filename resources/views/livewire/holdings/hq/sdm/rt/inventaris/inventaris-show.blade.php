<x-ui.sccr-card class="max-w-6xl mx-auto p-4">

    @if ($data)
        <div class="space-y-4">

            {{-- ================= HEADER ================= --}}
            <div class="bg-slate-900 rounded-xl p-4 shadow-lg border-l-4 border-green-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-boxes fa-6x text-white"></i>
                </div>

                <span class="text-[10px] font-bold text-green-400 uppercase tracking-[0.2em]">
                    Inventory Detail Label
                </span>

                <h2 class="text-3xl font-mono font-extrabold text-white mt-1">
                    {{ $data->kode_label }}
                </h2>

                @php
                    $statusColors = [
                        'Baik' => ['dot' => 'bg-green-500', 'text' => 'text-green-400'],
                        'Rusak' => ['dot' => 'bg-red-500', 'text' => 'text-red-400'],
                        'Hilang' => ['dot' => 'bg-gray-400', 'text' => 'text-gray-300'],
                        'Dalam Perbaikan' => ['dot' => 'bg-yellow-300', 'text' => 'text-yellow-300'],
                    ];
                    $c = $statusColors[$data->status] ?? ['dot' => 'bg-blue-500', 'text' => 'text-blue-400'];
                @endphp

                <div class="flex justify-between items-center text-xs mt-2">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $c['dot'] }} animate-pulse"></span>
                        <span class="{{ $c['text'] }}">{{ $data->status }}</span>
                    </div>
                    <span class="italic text-gray-300">
                        Terakhir Update:
                        {{ $data->tanggal_status ? \Carbon\Carbon::parse($data->tanggal_status)->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                {{-- LEFT --}}
                <div class="lg:col-span-4 space-y-4">
                    {{-- FOTO --}}
                    <div class="bg-gray-50 p-2 rounded-xl border shadow-sm overflow-hidden">
                        <div
                            class="relative aspect-square rounded-lg overflow-hidden bg-white flex items-center justify-center border">
                            @php
                                $photoPath = public_path('SDM/inventaris/foto/' . $data->foto);
                            @endphp

                            @if ($data->foto && file_exists($photoPath))
                                <img src="{{ asset('SDM/inventaris/foto/' . $data->foto) }}?v={{ time() }}"
                                    alt="Foto {{ $data->nama_barang }}"
                                    class="object-cover w-full h-full hover:scale-105 transition duration-500">
                            @else
                                <div class="text-center p-6">
                                    <i class="fas fa-image fa-4x text-gray-200 mb-2"></i>
                                    <p class="text-xs text-gray-400 font-medium">Foto Belum Tersedia</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- QR --}}
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">QR Code Label</h3>
                            <i class="fas fa-print text-gray-500"></i>
                        </div>

                        <div class="p-4">
                            <x-shared.sccr-qr-label wire:key="qr-{{ $data->kode_label }}" :value="$data->kode_label"
                                :label="$data->kode_label" class="w-full" />
                        </div>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="lg:col-span-8 space-y-4">
                    {{-- IDENTITAS --}}
                    <div class="bg-white border rounded-xl shadow-sm">
                        <div class="bg-gray-50 px-4 py-2 border-b text-sm font-bold uppercase">
                            Informasi Identitas
                        </div>

                        <div class="p-4 space-y-2 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Nama Barang</p>
                                <p class="font-semibold">{{ $data->nama_barang }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Jenis Barang</p>
                                <p class="font-semibold">{{ $data->nama_jenis }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Deskripsi</p>
                                <p>{{ $data->description ?? '-' }}</p>
                            </div>

                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-gray-400 text-center uppercase tracking-widest mb-1">
                                        Bulan Perolehan
                                    </p>
                                    <p class="font-semibold text-gray-800 text-center">
                                        {{ $data->Bulan ? \Carbon\Carbon::create()->month($data->Bulan)->format('F') : '00' }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[10px] font-bold text-gray-400 text-center uppercase tracking-widest mb-1">
                                        Tahun Perolehan
                                    </p>
                                    <p class="font-semibold text-gray-800 text-center">
                                        @if ($data->Tahun && $data->Tahun != '0')
                                            {{ \Carbon\Carbon::createFromFormat('y', str_pad($data->Tahun, 2, '0', STR_PAD_LEFT))->format('Y') }}
                                        @else
                                            0000
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LOKASI --}}
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="bg-gray-50 px-4 py-3 border-b text-sm font-bold text-gray-700 uppercase tracking-wider">
                            Lokasi & Penempatan
                        </div>

                        <div class="p-4 gap-y-2 gap-x-4 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">Holding
                                </p>
                                <p class="font-semibold text-gray-800">{{ $data->nama_holding }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">Lokasi</p>
                                <p class="font-semibold text-gray-800">{{ $data->nama_lokasi }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">Ruangan
                                </p>
                                <p class="font-semibold text-gray-800">{{ $data->nama_ruangan }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- DOKUMEN --}}
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Dokumen Digital</h3>
                            <i class="fas fa-file-pdf text-red-500"></i>
                        </div>

                        <div class="p-4">
                            @if ($data->dokumen && file_exists(public_path('SDM/inventaris/dokumen/' . $data->dokumen)))
                                <div
                                    class="flex items-center justify-between p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-invoice fa-2x text-blue-500 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-bold text-blue-900">{{ $data->dokumen }}</p>
                                            <p class="text-[10px] text-blue-700 uppercase">Dokumen PDF Terlampir</p>
                                        </div>
                                    </div>
                                    <a href="{{ asset('SDM/inventaris/dokumen/' . $data->dokumen) }}" target="_blank"
                                        class="bg-white text-blue-600 px-4 py-2 rounded shadow-sm text-xs font-bold hover:bg-blue-600 hover:text-white transition">
                                        Lihat PDF
                                    </a>
                                </div>
                            @else
                                <div
                                    class="text-center py-4 border-2 border-dashed rounded-lg text-gray-400 text-xs italic">
                                    Tidak ada lampiran dokumen PDF.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <x-ui.sccr-button variant="secondary" wire:click="$dispatch('inventaris-overlay-close')">
                    Kembali
                </x-ui.sccr-button>

                @permission('INV_UPDATE')
                    <x-ui.sccr-button variant="warning"
                        wire:click="$dispatch('inventaris-open-edit', { kodeLabel: '{{ $data->kode_label }}' })">
                        Edit Data
                    </x-ui.sccr-button>
                @endpermission
            </div>

        </div>
    @endif

</x-ui.sccr-card>
