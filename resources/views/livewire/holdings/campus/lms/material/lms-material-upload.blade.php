<x-sccr-card wire:key="lms-material-upload">

    {{-- Header --}}
    <div class="relative py-4 bg-yellow-600 rounded-b-3xl shadow-lg text-black z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Upload Materi</h1>
            <p class="text-lg">Untuk Room: <strong>{{ $room->name }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Room', 'url' => route('holdings.campus.lms.room.manage', $room->id)],
            // ['label' => 'Upload Materi', 'url' => route('holdings.campus.lms.material.upload', $room->id)],
        ]" />
    </div>

    {{-- Form Upload --}}
    <form wire:submit.prevent="upload" class="grid grid-cols-1 gap-4 px-4 py-6 max-w-xl">
        <x-sccr-input name="title" wire:model.defer="title" label="Judul Materi" />
        <x-sccr-input name="description" wire:model.defer="description" label="Deskripsi" type="textarea" />
        <x-sccr-select name="type" wire:model.defer="type" label="Tipe Materi" :options="[
            'document' => 'Dokumen',
            'video' => 'Video',
            'slide' => 'Slide',
            'link' => 'Tautan',
        ]" />
        <x-sccr-input name="file_path" wire:model.defer="file_path" label="Path File / URL" />

        <div class="flex gap-3 mt-4">
            <x-sccr-button type="submit" variant="primary">Upload</x-sccr-button>
            <x-sccr-button type="button" wire:click="$reset" variant="secondary">Reset</x-sccr-button>
        </div>

        @if (session()->has('success'))
            <div class="text-green-600 font-semibold mt-4">{{ session('success') }}</div>
        @endif
    </form>

</x-sccr-card>
