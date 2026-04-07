<x-sccr-card wire:key="lms-room-create">

    {{-- Background --}}
    <div class="fixed top-0 opacity-30 -z-10">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-200 object-scale-down">
    </div>

    {{-- Header --}}
    <div class="relative py-4 bg-indigo-500 rounded-b-3xl shadow-lg text-white z-10">
        <div class="flex justify-between items-center px-4 mb-2">
            <h1 class="text-3xl md:text-4xl font-bold">Buat Room LMS</h1>
            <h1>Dosen: <span class="font-semibold">{{ auth()->user()->nama }}</span></h1>
        </div>
        <p class="text-lg px-4">Formulir pembuatan ruang LMS untuk kuliah daring</p>

        {{-- Breadcrumb --}}
        <div class="text-sm px-4 mt-2">
            <x-sccr-breadcrumb :items="[
                ['label' => 'Main Dashboard', 'url' => route('dashboard')],
                ['label' => 'Campus', 'url' => route('dashboard.campus')],
                ['label' => 'LMS', 'url' => route('dashboard.lms-main')],
                ['label' => 'Buat Room', 'url' => route('holdings.campus.lms.room.create')],
            ]" />
        </div>
    </div>

    {{-- Form --}}
    <form wire:submit.prevent="save" class="grid grid-cols-1 gap-4 px-4 py-6 max-w-xl">
        <x-sccr-input name="name" wire:model.defer="name" label="Nama Room"
            placeholder="Contoh: Pemrograman Web A" />
        @error('name')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <x-sccr-input name="kurikulum" wire:model.defer="kurikulum" label="Kurikulum" placeholder="Contoh: RPL 2025" />
        @error('kurikulum')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <x-sccr-input name="semester" wire:model.defer="semester" label="Semester"
            placeholder="Contoh: Ganjil 2025/2026" />
        @error('semester')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <x-sccr-input type="number" name="max_participants" wire:model.defer="max_participants" label="Max Peserta"
            placeholder="Default: 10000" />
        @error('max_participants')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <div class="flex gap-3 mt-4">
            <x-sccr-button type="submit" variant="primary">Buat Room</x-sccr-button>
            <x-sccr-button type="button" wire:click="$reset" variant="secondary">Reset</x-sccr-button>
        </div>

        @if (session()->has('success'))
            <div class="text-green-600 font-semibold mt-4">{{ session('success') }}</div>
        @endif
    </form>

</x-sccr-card>
