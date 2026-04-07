{{-- <div>
    <form wire:submit.prevent="save" class="space-y-4">
        @if (session()->has('success'))
            <div class="p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif --}}

{{-- NIP (Manual Input) --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">NIP <span class="text-red-500">*</span></label>
            <input type="text" wire:model="nip"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                placeholder="Contoh: 20180205 1 001">
            @error('nip')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Nama --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">Nama <span class="text-red-500">*</span></label>
            <input type="text" wire:model="nama"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                placeholder="Nama lengkap">
            @error('nama')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Holding --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">Holding <span class="text-red-500">*</span></label>
            <select wire:model="holding_id"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">-- Pilih Holding --</option>
                @foreach ($holdings as $holding)
                    <option value="{{ $holding->id }}">{{ $holding->name }}</option>
                @endforeach
            </select>
            @error('holding_id')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Department --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">Departemen <span class="text-red-500">*</span></label>
            <select wire:model="department_id"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">-- Pilih Departemen --</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Position --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">Posisi <span class="text-red-500">*</span></label>
            <select wire:model="position_id"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">-- Pilih Posisi --</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->title }}</option>
                @endforeach
            </select>
            @error('position_id')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Tanggal Join --}}
{{-- <div>
            <label class="block text-sm font-medium mb-1">Tanggal Join <span class="text-red-500">*</span></label>
            <input type="date" wire:model="tanggal_join"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
            @error('tanggal_join')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div> --}}

{{-- Submit Button --}}
{{-- <div class="pt-3">
            <button type="submit"
                class="px-6 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 font-medium focus:ring-2 focus:ring-yellow-400">
                💾 Simpan
            </button>
        </div>
    </form>
</div> --}}


<div>
    <form wire:submit.prevent="save" class="space-y-4">
        @if (session()->has('success'))
            <div class="p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- NIP (Menggunakan x-sccr-input) --}}
        {{-- Atribut required ditambahkan melalui prop :required="true" --}}
        <x-sccr-input name="nip" label="NIP" wire:model="nip" :required="true"
            placeholder="Contoh: 20180205 1 001" />

        {{-- Nama (Menggunakan x-sccr-input) --}}
        <x-sccr-input name="nama" label="Nama" wire:model="nama" :required="true" placeholder="Nama lengkap" />

        {{-- Holding (Menggunakan x-sccr-select) --}}
        {{-- Variabel $holdings, $departments, $positions diasumsikan sudah berupa objek koleksi (Model) di Livewire Component. --}}
        {{-- Kita perlu mengubah koleksi objek menjadi array key=>value untuk x-sccr-select. --}}
        <x-sccr-select name="holding_id" label="Holding" wire:model="holding_id" :required="true" :options="$holdings->pluck('name', 'id')->toArray()" />

        {{-- Department (Menggunakan x-sccr-select) --}}
        <x-sccr-select name="department_id" label="Departemen" wire:model="department_id" :required="true"
            :options="$departments->pluck('name', 'id')->toArray()" />

        {{-- Position (Menggunakan x-sccr-select) --}}
        <x-sccr-select name="position_id" label="Posisi" wire:model="position_id" :required="true" :options="$positions->pluck('title', 'id')->toArray()" />

        {{-- Tanggal Join (Menggunakan x-sccr-input type="date") --}}
        <x-sccr-input name="tanggal_join" label="Tanggal Join" wire:model="tanggal_join" type="date"
            :required="true" />

        <div class="pt-3 flex justify-end space-x-3">
            <button type="button" wire:click="confirmCancel"
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-medium focus:ring-2 focus:ring-gray-400">
                ❌ Batal
            </button>

            <button type="submit"
                class="px-6 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 font-medium focus:ring-2 focus:ring-yellow-400">
                💾 Simpan
            </button>
        </div>

        <x-sccr-toast :show="$showCancelConfirm" type="warning" message="Yakin batal tambah data?" />

        @if ($showCancelConfirm)
            <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded shadow-lg text-center">
                    <p class="text-lg font-semibold mb-4">Yakin batal tambah data?</p>
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
