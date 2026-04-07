@props([
    'show' => false,
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin ingin melanjutkan aksi ini?',
    'confirmLabel' => 'Lanjutkan',
    'cancelLabel' => 'Batal',
    'confirmAction' => null, // wire:click target
])

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 animate-fade-in">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ $title }}</h2>

            <div class="text-sm text-gray-600 mb-6">
                {{ $slot->isEmpty() ? $message : $slot }}
            </div>

            <div class="flex justify-end gap-3">
                <x-ui.sccr-button wire:click="$set('showConfirmModal', false)" variant="secondary">
                    {{ $cancelLabel }}
                </x-ui.sccr-button>

                @if ($confirmAction)
                    <x-ui.sccr-button wire:click="{{ $confirmAction }}" variant="danger">
                        {{ $confirmLabel }}
                    </x-ui.sccr-button>
                @endif
            </div>
        </div>
    </div>
@endif
