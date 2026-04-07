<div class="p-6">
    <div class="lg:col-span-5 flex flex-col min-h-0">
        <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Tambah Baru</h3>

        <div class="bg-gray-50/70 border rounded-2xl p-5 flex-1 overflow-y-auto">
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="text-xs font-bold text-gray-700">Holding</label>
                    <x-ui.sccr-select name="holdingKode" wire:model.defer="holdingKode" :options="$holdingOptions" />
                </div>

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-4">
                        <label class="text-xs font-bold text-gray-700">Kode Lokasi</label>
                        <x-ui.sccr-input name="lokasiKode" wire:model.defer="lokasiKode" maxlength="2"
                            placeholder="01" />
                    </div>

                    <div class="col-span-8">
                        <label class="text-xs font-bold text-gray-700">Nama Lokasi</label>
                        <x-ui.sccr-input name="namaLokasi" wire:model.defer="namaLokasi" placeholder="Nama..." />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ui.sccr-button type="button" variant="success" wire:click="save">
                        🚀 Simpan Data
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" variant="secondary"
                        wire:click="$dispatch('inv-master-lokasi-overlay-close')">
                        Batal
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    </div>
</div>
