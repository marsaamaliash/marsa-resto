@props([
    'createRoute' => null,
    'importAction' => null,
    'exportAction' => null,
    'template' => null,
])

<div class="bg-white rounded shadow p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        {{-- Aksi --}}
        <div class="flex gap-2">
            @if ($importAction)
                <form wire:submit.prevent="importFile" enctype="multipart/form-data">
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                        class="block text-sm text-gray-700 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                        required>
                    <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        Import
                    </button>
                </form>
            @endif

            @if ($exportAction)
                <button wire:click="exportFile"
                    class="px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                    Export
                </button>
            @endif

            @if ($createRoute)
                <a href="{{ $createRoute }}"
                    class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    + Tambah
                </a>
            @endif
        </div>
    </div>

    @if ($template)
        <p class="mt-4 text-sm text-gray-600">
            Unduh <a href="{{ $template }}" class="text-blue-600 underline">template Excel</a> sebelum import.
        </p>
    @endif
</div>
