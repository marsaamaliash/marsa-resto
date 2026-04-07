<div class="p-1">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-green-600 to-emerald-700 rounded-xl p-4 mb-6 shadow-md text-white">
        <h2 class="text-xl font-bold flex items-center">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Inventaris Baru
        </h2>
        <p class="text-green-100 text-xs mt-1">
            Sistem akan mengenerate nomor urut otomatis berdasarkan kombinasi kode yang dipilih.
        </p>
    </div>

    <form wire:submit.prevent="store">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- KIRI: INPUT FORM --}}
            <div class="lg:col-span-8 space-y-4">

                {{-- AB --}}
                <div>
                    <label class="text-sm font-bold text-gray-700">
                        Holding &rarr; <span class="text-green-600">AB</span>
                    </label>
                    <select wire:model.live="holding_kode" class="w-full border-gray-300 rounded-lg text-sm shadow-sm">
                        <option value="">-- Pilih Holding --</option>
                        @foreach ($data_holding as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('holding_kode')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- CD --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">
                            Lokasi &rarr; <span class="text-green-600">CD</span>
                        </label>

                        <select wire:model.live="lokasi_kode"
                            class="w-full border-gray-300 rounded-lg text-sm shadow-sm" @disabled(empty($this->lokasiOptions))>
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($this->lokasiOptions as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>

                        @error('lokasi_kode')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- EF --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">
                            Ruangan &rarr; <span class="text-green-600">EF</span>
                        </label>

                        <select wire:model.live="ruangan_kode"
                            class="w-full border-gray-300 rounded-lg text-sm shadow-sm" @disabled(empty($this->ruanganOptions))>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach ($this->ruanganOptions as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>

                        <div wire:loading wire:target="lokasi_kode" class="text-[10px] text-blue-500 italic">
                            Memuat data ruangan...
                        </div>

                        @error('ruangan_kode')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- GH --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">
                            Jenis Barang &rarr; <span class="text-green-600">GH</span>
                        </label>
                        <select wire:model.live="jenis_barang_kode"
                            class="w-full border-gray-300 rounded-lg text-sm shadow-sm">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach ($data_jenis_barang as $kode => $nama)
                                <option value="{{ $kode }}">{{ $kode }} -- {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_barang_kode')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- QTY --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">Jumlah Barang</label>
                        <div class="relative">
                            <input type="number" wire:model.live.debounce.500ms="qty"
                                class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-green-500 focus:border-green-500"
                                min="1" max="500" placeholder="1">
                            <div wire:loading wire:target="qty" class="absolute right-3 top-2">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </div>
                        </div>
                        @error('qty')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- INFO IJK --}}
                <div class="p-2 bg-gray-50 border rounded-lg">
                    <label class="text-sm font-bold text-gray-700">
                        Nomor Urut Barang &rarr; <span class="text-purple-600">IJK</span>
                    </label>
                    <div class="text-xs font-mono text-purple-700 mt-1">
                        &rarr; Terakhir: <b>{{ str_pad($no_urut_terakhir, 3, '0', STR_PAD_LEFT) }}</b>,
                        Berikutnya:
                        <b>{{ str_pad($no_urut_mulai > 0 ? $no_urut_mulai : 1, 3, '0', STR_PAD_LEFT) }}</b>,
                        Sampai:
                        <b>{{ str_pad(($no_urut_mulai > 0 ? $no_urut_mulai : 1) + (int) $qty - 1, 3, '0', STR_PAD_LEFT) }}</b>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- LM --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">
                            Bulan Pembelian &rarr; <span class="text-green-600">LM</span>
                        </label>
                        <select wire:model.live="bulan" class="w-full border-gray-300 rounded-lg text-sm shadow-sm">
                            <option value="00">00 -- Tidak Diketahui</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }} -- {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NO --}}
                    <div>
                        <label class="text-sm font-bold text-gray-700">
                            Tahun Pembelian &rarr; <span class="text-green-600">NO</span>
                        </label>
                        <select wire:model.live="tahun" class="w-full border-gray-300 rounded-lg text-sm shadow-sm">
                            <option value="00">00 -- Tidak Diketahui</option>
                            @for ($y = date('Y'); $y >= 2010; $y--)
                                <option value="{{ substr($y, -2) }}">{{ substr($y, -2) }} -- {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-700">Nama Barang</label>
                    <input type="text" wire:model="nama_barang" class="w-full border-gray-300 rounded-lg text-sm"
                        placeholder="Contoh: Dispenser Sharp">
                    @error('nama_barang')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-700">Description</label>
                    <textarea wire:model="description" class="w-full border-gray-300 rounded-lg text-sm" rows="2"></textarea>
                </div>
            </div>

            {{-- KANAN: PREVIEW --}}
            <div class="lg:col-span-4">
                <div class="bg-white border rounded-xl p-4 sticky top-4 text-center">
                    <p class="text-sm font-bold text-gray-500 mb-2">Preview Kode Label</p>

                    <div class="font-mono font-bold text-sm mb-4">
                        <span class="text-green-600">AB</span>.<span class="text-green-600">CDEFGH</span><span
                            class="text-purple-600">IJK</span>.<span class="text-green-600">LMNO</span>
                    </div>

                    <div class="bg-gray-50 border-2 border-dashed rounded-xl p-4 flex flex-col items-center">
                        <div class="bg-white p-4 shadow-sm rounded-lg flex items-center gap-4">
                            <img src="{{ asset('images/logoSCCR.png') }}" class="h-10" alt="Logo">
                            <img src="{{ route('sso.qr.generate', ['q' => $this->generatedLabel, 's' => 200]) }}"
                                class="w-20 h-20" alt="QR">
                        </div>

                        <div class="mt-4 font-mono font-bold text-green-700 text-lg tracking-tighter">
                            {{ $this->generatedLabel }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
            <button type="button" wire:click="cancel"
                class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-lg transition">
                Batal
            </button>

            <button type="submit"
                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md transition"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Simpan Inventaris</span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...
                </span>
            </button>
        </div>
    </form>

    {{-- MODAL KONFIRMASI CETAK --}}
    @if ($showPrintConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                <div
                    class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Berhasil Disimpan!</h3>
                <p class="text-gray-500 mt-2 text-sm">
                    Berhasil membuat {{ (int) $qty }} data inventaris. Cetak label QR Code sekarang?
                </p>

                <div class="mt-6 flex flex-col gap-2">
                    <button type="button" onclick="runPrintProcess(@js($lastCreatedLabels))"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition">
                        <i class="fas fa-print mr-2"></i> YA, Cetak Sekarang
                    </button>

                    <button type="button" wire:click="closeAndGoHome"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-3 rounded-xl transition">
                        TIDAK, Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
    <script>
        window.runPrintProcess = function(labels) {
            if (!labels || labels.length === 0) return;

            const logoPath = "{{ asset('images/logoSCCR.png') }}";

            let style = `<style>
            @page { size: 102mm 25mm; margin: 0; }
            html, body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }
            .print-row { width: 102mm; height: 25mm; display: flex; page-break-after: always; overflow: hidden; }
            .label { width: 50mm; height: 25mm; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; }
            .top { display: flex; align-items: center; justify-content: center; gap: 3mm; margin-top: 3mm; }
            .logo { max-height: 12mm; width: auto; }
            .qr-img { height: 12mm; width: 12mm; }
            .bottom { text-align: center; font-weight: bold; font-size: 3.5mm; margin-bottom: 2.5mm; font-family: Arial, sans-serif; }
            .gap { width: 2mm; }
        </style>`;

            let content = '';

            for (let i = 0; i < labels.length; i += 2) {
                content += '<div class="print-row">';
                content += generateLabelHtml(labels[i], logoPath);

                if (labels[i + 1]) {
                    content += '<div class="gap"></div>';
                    content += generateLabelHtml(labels[i + 1], logoPath);
                }

                content += '</div>';
            }

            function generateLabelHtml(kode, logo) {
                let qrUrl = "{{ route('sso.qr.generate') }}?q=" + encodeURIComponent(kode) + "&s=300";
                return `
                <div class="label">
                    <div class="top">
                        <img src="${logo}" class="logo">
                        <img src="${qrUrl}" class="qr-img">
                    </div>
                    <div class="bottom">${kode}</div>
                </div>`;
            }

            const win = window.open('', '_blank');
            win.document.write('<html><head><title>Print Label</title>' + style + '</head><body>' + content +
                '</body></html>');
            win.document.close();

            setTimeout(() => {
                win.focus();
                win.print();
                win.close();
                $wire.closeAndGoHome();
            }, 1200);
        };
    </script>
@endscript
