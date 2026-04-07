<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Absensi Lokasi
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($toast['show'])
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                    class="mb-4 p-4 rounded-lg border {{ $toast['type'] === 'success' ? 'bg-green-100 border-green-300 text-green-800' : 'bg-red-100 border-red-300 text-red-800' }}">
                    <p class="font-semibold">{{ $toast['message'] }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6 space-y-6">
                <div class="space-y-2">
                    <label for="id_holding" class="block text-sm text-white font-medium">Pilih Kantor</label>
                    <select id="id_holding" wire:model.live="id_holding"
                        class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        @foreach ($holdings as $office)
                            <option value="{{ $office->id_holding }}" data-lat="{{ $office->latitude }}"
                                data-lon="{{ $office->longitude }}" data-radius="{{ $office->radius_meter }}">
                                {{ $office->nama_holding }} (radius {{ $office->radius_meter }} m)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button id="btn-in"
                        class="py-3 px-4 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700"
                        wire:loading.attr="disabled" wire:target="absen">
                        Absen Masuk
                    </button>
                    <button id="btn-out"
                        class="py-3 px-4 rounded-2xl bg-indigo-600 text-white font-semibold hover:bg-indigo-600"
                        wire:loading.attr="disabled" wire:target="absen">
                        Absen Pulang
                    </button>
                </div>

                <div wire:loading wire:target="absen" class="text-center text-gray-500">
                    Memproses absensi...
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    * Pastikan GPS aktif. Aplikasi akan mengambil lokasi Anda saat menekan tombol.
                </p>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6">
                <h3 class="font-semibold mb-3 text-white">Riwayat Hari Ini</h3>
                @if ($todayLogs->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada absensi hari ini.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($todayLogs as $log)
                            <li class="text-sm">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-white {{ $log->jenis === 'In' ? 'bg-blue-600' : 'bg-indigo-600' }}">
                                    {{ $log->jenis === 'In' ? 'Masuk' : 'Pulang' }}
                                </span>
                                <span class="ml-2">{{ $log->holding->nama_holding }}</span>
                                <span class="ml-2 text-gray-500">{{ $log->jam }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/absensipwa.js'])
