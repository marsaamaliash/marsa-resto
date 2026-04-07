<div class="p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Master Account • Edit
            </div>
            <h3 class="text-lg font-extrabold text-gray-900">
                Edit {{ $code ?? '' }}
            </h3>
            <p class="text-xs text-gray-500 mt-1">
                Holding/Dept/Div & CoA Code dikunci. Ubah nama account saja.
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-bold text-gray-700">Holding</label>
                    <x-ui.sccr-input name="holdingName" value="{{ $holdingName ?? '-' }}" disabled />
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700">Department</label>
                    <x-ui.sccr-input name="departmentName" value="{{ $departmentName ?? '-' }}" disabled />
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700">Division</label>
                    <x-ui.sccr-input name="divisionName" value="{{ $divisionName ?? '-' }}" disabled />
                </div>
            </div>

            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-4">
                    <label class="text-xs font-bold text-gray-700">CoA Code</label>
                    <x-ui.sccr-input name="code" wire:model.defer="code" disabled />
                </div>

                <div class="col-span-8">
                    <label class="text-xs font-bold text-gray-700">Nama Akun</label>
                    <x-ui.sccr-input name="name" wire:model.defer="name" placeholder="Nama akun..." />
                    @error('name')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui.sccr-button type="button" variant="success" wire:click="save">
                    💾 Simpan Perubahan
                </x-ui.sccr-button>

                <x-ui.sccr-button type="button" variant="secondary"
                    wire:click="$dispatch('fin-master-account-overlay-close')">
                    Batal
                </x-ui.sccr-button>
            </div>
        </div>
    </div>
</div>
