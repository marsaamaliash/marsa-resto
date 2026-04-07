<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modul Absensi
        </h2>
        <p class="text-sm text-gray-500">Kelola data absensi dari mesin Finger Print.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if ($toast['show'])
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                    class="mb-4 p-4 rounded-lg border {{ $toast['type'] === 'success' ? 'bg-green-100 border-green-300 text-green-800' : ($toast['type'] === 'error' ? 'bg-red-100 border-red-300 text-red-800' : 'bg-yellow-100 border-yellow-300 text-yellow-800') }}">
                    <p class="font-semibold">{{ $toast['message'] }}</p>
                </div>
            @endif

            <div x-data="{ tab: @entangle('activeTab') }" class="bg-white shadow-lg rounded-2xl overflow-hidden">
                <div class="flex border-b">
                    <button @click="tab = 'upload'"
                        :class="tab === 'upload' ? 'border-b-4 border-red-600 text-red-600 font-bold' : 'text-gray-600 hover:text-red-600'"
                        class="flex-1 py-3 text-center transition-all">
                        Upload & Generate Excel
                    </button>
                    <button @click="tab = 'dashboard'"
                        :class="tab === 'dashboard' ? 'border-b-4 border-red-600 text-red-600 font-bold' : 'text-gray-600 hover:text-red-600'"
                        class="flex-1 py-3 text-center transition-all">
                        Dashboard Data
                    </button>
                    <button @click="tab = 'download'"
                        :class="tab === 'download' ? 'border-b-4 border-red-600 text-red-600 font-bold' : 'text-gray-600 hover:text-red-600'"
                        class="flex-1 py-3 text-center transition-all">
                        Download Data
                    </button>
                </div>

                <div class="p-6">
                    <div x-show="tab === 'upload'" x-cloak>
                        <h2 class="text-xl font-semibold mb-4">Upload File Absensi (Excel)</h2>
                        <div class="space-y-4">
                            <input type="file" wire:model="uploadedFile" accept=".xls,.xlsx"
                                class="block w-full border p-2 rounded-lg">
                            @error('uploadedFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="uploadedFile" class="text-sm text-gray-500">File sedang diproses...</div>
                            <button wire:click="upload" wire:loading.attr="disabled" wire:target="upload"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow disabled:opacity-50"
                                @if(!$canUpload) disabled title="Tidak punya izin upload" @endif>
                                Upload & Generate
                            </button>
                        </div>
                    </div>

                    <div x-show="tab === 'dashboard'" x-cloak>
                        <h2 class="text-xl font-semibold mb-4">Dashboard Data Absensi</h2>
                        <p class="text-gray-600 mb-4">Preview data hasil upload sebelum di-generate ke DB.</p>

                        @if (!empty($previewData))
                            <div class="mb-4">
                                <div class="max-h-96 overflow-y-auto border rounded-lg p-2">
                                    <h5 class="mb-2 font-bold">
                                        Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
                                    </h5>
                                    <div class="max-h-[500px] overflow-y-auto">
                                        <table class="min-w-full border-collapse text-sm">
                                            <thead class="sticky top-0 bg-gray-100">
                                                <tr>
                                                    <th class="border px-3 py-2 text-left">No</th>
                                                    <th class="border px-3 py-2 text-left">Nama</th>
                                                    <th class="border px-3 py-2 text-left">Dept</th>
                                                    <th class="border px-3 py-2 text-left">Tanggal</th>
                                                    <th class="border px-3 py-2 text-left">Jam Masuk</th>
                                                    <th class="border px-3 py-2 text-left">Jam Keluar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($previewData as $row)
                                                    <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-100' }}">
                                                        <td class="px-3 py-1">{{ $row['no'] }}</td>
                                                        <td class="px-3 py-1">{{ $row['nama'] }}</td>
                                                        <td class="px-3 py-1">{{ $row['dept'] }}</td>
                                                        <td class="px-3 py-1">{{ $row['tanggal'] }}</td>
                                                        <td class="px-3 py-1">{{ $row['jam_masuk'] }}</td>
                                                        <td class="px-3 py-1">{{ $row['jam_keluar'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <button wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow disabled:opacity-50"
                                @if(!$canGenerate) disabled title="Tidak punya izin generate" @endif>
                                Generate ke DB
                            </button>

                            @if (!empty($generateResult))
                                <div class="mt-4 p-4 bg-green-100 border border-green-300 rounded-lg">
                                    <p class="font-semibold text-green-800">Data absensi berhasil digenerate ke database.</p>
                                </div>

                                @if (!empty($generateResult['baru']))
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="font-semibold text-blue-700 mb-2">Data Baru ({{ count($generateResult['baru']) }} baris):</p>
                                        <ul class="list-disc pl-5 text-sm">
                                            @foreach (array_slice($generateResult['baru'], 0, 10) as $row)
                                                <li>{{ $row['nama'] }} - {{ $row['tanggal'] }} (Masuk: {{ $row['jam_masuk'] ?? '-' }}, Keluar: {{ $row['jam_keluar'] ?? '-' }})</li>
                                            @endforeach
                                            @if (count($generateResult['baru']) > 10)
                                                <li class="text-gray-500">... dan {{ count($generateResult['baru']) - 10 }} baris lainnya</li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif

                                @if (!empty($generateResult['sudah_ada']))
                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="font-semibold text-yellow-700 mb-2">Data Sudah Ada ({{ count($generateResult['sudah_ada']) }} baris, tidak disimpan ulang):</p>
                                        <ul class="list-disc pl-5 text-sm">
                                            @foreach (array_slice($generateResult['sudah_ada'], 0, 10) as $row)
                                                <li>{{ $row['nama'] }} - {{ $row['tanggal'] }} (Masuk: {{ $row['jam_masuk'] ?? '-' }}, Keluar: {{ $row['jam_keluar'] ?? '-' }})</li>
                                            @endforeach
                                            @if (count($generateResult['sudah_ada']) > 10)
                                                <li class="text-gray-500">... dan {{ count($generateResult['sudah_ada']) - 10 }} baris lainnya</li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        @else
                            <p class="text-gray-500 italic">Belum ada data preview. Silakan upload file terlebih dahulu.</p>
                        @endif
                    </div>

                    <div x-show="tab === 'download'" x-cloak>
                        <h2 class="text-xl font-semibold mb-4">Download Data Absensi</h2>
                        <p class="text-gray-600 mb-4">Data hasil generate siap diunduh ke Excel.</p>
                        <button wire:click="download" wire:loading.attr="disabled" wire:target="download"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow disabled:opacity-50"
                            @if(!$canDownload) disabled title="Tidak punya izin download" @endif>
                            Download ke Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-8 text-center">
        <a href="{{ route('dashboard') }}"
            class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-full transition duration-300">
            Kembali ke Dashboard
        </a>
    </div>
</div>
