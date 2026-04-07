<x-sccr-card wire:key="lms-quiz-create">

    {{-- Header --}}
    <div class="relative py-4 bg-purple-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Buat Kuis</h1>
            <p class="text-lg">Untuk Room: <strong>{{ $room->name }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Room', 'url' => route('holdings.campus.lms.room.manage', $room->id)],
            // ['label' => 'Buat Kuis', 'url' => route('holdings.campus.lms.quiz.create', $room->id)],
        ]" />
    </div>

    {{-- Form --}}
    <form wire:submit.prevent="save" class="grid grid-cols-1 gap-4 px-4 py-6 max-w-xl">
        <x-sccr-input name="title" wire:model.defer="title" label="Judul Kuis" />
        @error('title')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <x-sccr-input name="instructions" wire:model.defer="instructions" label="Instruksi" type="textarea" />

        <x-sccr-input name="start_time" wire:model.defer="start_time" label="Waktu Mulai" type="datetime-local" />
        @error('start_time')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <x-sccr-input name="end_time" wire:model.defer="end_time" label="Waktu Selesai" type="datetime-local" />
        @error('end_time')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror

        <div class="flex gap-3 mt-4">
            <x-sccr-button type="submit" variant="primary">Simpan Kuis</x-sccr-button>
            <x-sccr-button type="button" wire:click="$reset" variant="secondary">Reset</x-sccr-button>
        </div>

        @if (session()->has('success'))
            <div class="text-green-600 font-semibold mt-4">{{ session('success') }}</div>
        @endif
    </form>

</x-sccr-card>
