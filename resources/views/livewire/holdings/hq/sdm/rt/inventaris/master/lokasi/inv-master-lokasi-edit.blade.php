<div class="p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Master Lokasi • Edit
            </div>
            <h3 class="text-lg font-extrabold text-gray-900">
                Edit {{ $holdingKode }}.{{ $lokasiKode }}
            </h3>
            <p class="text-xs text-gray-500 mt-1">
                Kode Holding & Kode Lokasi dikunci. Ubah nama lokasi saja.
            </p>
        </div>

        <div class="shrink-0">
            <x-ui.sccr-badge type="info">
                EDIT MODE
            </x-ui.sccr-badge>
        </div>
    </div>

    <div class="bg-gray-50/70 border rounded-2xl p-5">
        <div class="grid grid-cols-1 gap-4">

            <div>
                <label class="text-xs font-bold text-gray-700">Holding</label>
                <x-ui.sccr-input name="holdingLabel" value="{{ $holdingKode }} - {{ $namaHolding }}" disabled />
            </div>
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-4">
                    <label class="text-xs font-bold text-gray-700">Kode Lokasi</label>
                    <x-ui.sccr-input name="lokasiKode" wire:model.defer="lokasiKode" disabled />
                </div>

                <div class="col-span-8">
                    <label class="text-xs font-bold text-gray-700">Nama Lokasi</label>
                    <x-ui.sccr-input name="namaLokasi" wire:model.defer="namaLokasi" placeholder="Nama lokasi..." />
                    @error('namaLokasi')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui.sccr-button type="button" variant="success" wire:click="save">
                    💾 Simpan Perubahan
                </x-ui.sccr-button>

                <x-ui.sccr-button type="button" variant="secondary"
                    wire:click="$dispatch('inv-master-lokasi-overlay-close')">
                    Batal
                </x-ui.sccr-button>
            </div>
        </div>
    </div>
</div>
