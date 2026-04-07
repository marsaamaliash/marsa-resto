<div>
    <form wire:submit.prevent="update" class="space-y-4">
        @if (session()->has('success'))
            <div class="p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- UI lama dipertahankan, tapi pakai namespace komponen baru --}}
        <x-ui.sccr-modal-layout title="Edit Data Karyawan" :groups="[
            'tab1' => 'Data Pribadi',
            'tab2' => 'Alamat & Info',
            'tab3' => 'Kontak & BPJS',
            'tab4' => 'Keuangan',
        ]" active="tab1">
            <x-slot name="tab1">
                @include('livewire.holdings.hq.sdm.hr.partials.tab1')
            </x-slot>

            <x-slot name="tab2">
                @include('livewire.holdings.hq.sdm.hr.partials.tab2')
            </x-slot>

            <x-slot name="tab3">
                @include('livewire.holdings.hq.sdm.hr.partials.tab3')
            </x-slot>

            <x-slot name="tab4">
                @include('livewire.holdings.hq.sdm.hr.partials.tab4')
            </x-slot>

            <x-slot name="buttons">
                <button type="button" wire:click="confirmCancel"
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-medium focus:ring-2 focus:ring-gray-400">
                    ❌ Batal
                </button>

                <button type="submit"
                    class="px-6 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 font-medium focus:ring-2 focus:ring-yellow-400">
                    💾 Simpan
                </button>
            </x-slot>
        </x-ui.sccr-modal-layout>

        {{-- Toast versi baru --}}
        <x-ui.sccr-toast :show="$showCancelConfirm" type="warning" message="Yakin batal edit data?" />

        @if ($showCancelConfirm)
            <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded shadow-lg text-center">
                    <p class="text-lg font-semibold mb-4">Yakin batal edit data?</p>
                    <div class="flex justify-center space-x-4">
                        <button wire:click="cancel" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Ya, Batalkan
                        </button>
                        <button wire:click="$set('showCancelConfirm', false)"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Tidak
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>
