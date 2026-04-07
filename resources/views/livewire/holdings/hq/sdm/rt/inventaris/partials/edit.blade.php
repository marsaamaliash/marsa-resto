<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <div class="p-2">

            {{-- Label Identity Card --}}
            <div
                class="mb-6 bg-gradient-to-r from-gray-900 to-gray-800 p-5 rounded-xl border-l-8 border-green-500 shadow-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] text-green-400 font-black tracking-[0.2em] uppercase">Inventory Label</p>
                        <h2 class="text-4xl font-mono font-bold text-white mt-1">{{ $kode_label }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 uppercase">Status</p>
                        <span
                            class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold
                            {{ $status == 'Baik' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="update" class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- LEFT: Read-only structure --}}
                <div class="lg:col-span-5 space-y-4 bg-gray-50 p-5 rounded-xl border border-gray-200 h-fit">
                    <h3
                        class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b pb-2 text-red-400">
                        Label AB.CDEFGHIJK (Read-Only)
                    </h3>

                    <x-ui.sccr-input name="ab_display" label="1. Holding → AB" :value="$ab . ' - ' . $holding_nama" readonly
                        class="bg-gray-100 text-red-400" />

                    <x-ui.sccr-input name="cd_display" label="2. Lokasi → CD" :value="$cd . ' - ' . $lokasi_nama" readonly
                        class="bg-gray-100 text-red-400" />

                    <x-ui.sccr-input name="ef_display" label="3. Ruangan → EF" :value="$ef . ' - ' . $ruangan_nama" readonly
                        class="bg-gray-100 text-red-400" />

                    <x-ui.sccr-input name="gh_display" label="4. Jenis → GH" :value="$gh . ' - ' . $jenis_nama" readonly
                        class="bg-gray-100 text-red-400" />

                    <x-ui.sccr-input name="ijk" label="5. No Urut → IJK" wire:model="ijk" readonly
                        class="bg-gray-100 font-mono text-center font-bold text-red-400" />

                    <div class="border-t"></div>

                    {{-- Bulan --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">
                            6. Bulan Pembelian <span class="text-green-700 font-mono">→ LM</span>
                        </label>
                        <select wire:model.live="Bulan"
                            class="w-full border-gray-300 rounded-lg shadow-sm font-medium focus:ring-blue-500 focus:border-blue-500">
                            <option value="00">N/A (00)</option>
                            @foreach (range(1, 12) as $m)
                                @php $val = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                <option value="{{ $val }}">
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }} ({{ $val }})
                                </option>
                            @endforeach
                        </select>
                        @error('Bulan')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tahun --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">
                            7. Tahun Pembelian <span class="text-green-700 font-mono">→ NO</span>
                            <span class="normal-case font-normal text-blue-600">({{ $tahun_display }})</span>
                        </label>
                        <select wire:model.live="Tahun"
                            class="w-full border-gray-300 rounded-lg shadow-sm font-medium focus:ring-blue-500 focus:border-blue-500">
                            <option value="00">N/A (0000)</option>
                            @for ($y = date('Y'); $y >= 2020; $y--)
                                @php $val = substr($y, -2); @endphp
                                <option value="{{ $val }}">{{ $y }} ({{ $val }})</option>
                            @endfor
                        </select>
                        @error('Tahun')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- RIGHT: Editable fields & uploads --}}
                <div class="lg:col-span-7 space-y-4">
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-2 mb-4">
                            Informasi Detail Barang
                        </h3>

                        <x-ui.sccr-input name="nama_barang" label="Nama Barang" wire:model="nama_barang" />
                        @error('nama_barang')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-gray-700">Deskripsi Barang</label>
                        <textarea wire:model="description" name="description" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 text-sm"></textarea>
                        @error('description')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- FILES --}}
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 space-y-6">
                        <h3
                            class="text-xs font-bold text-blue-700 uppercase tracking-wider border-b border-blue-200 pb-2 mb-2">
                            <i class="fas fa-paperclip mr-1"></i> File Pendukung
                        </h3>

                        {{-- FOTO --}}
                        <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <x-ui.sccr-input type="file" label="Foto Barang (PNG/JPG)" name="foto"
                                wire:model="foto" accept="image/png, image/jpeg" />

                            <div class="mt-2 flex items-center space-x-4">
                                <div x-show="isUploading" class="flex-1 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                        :style="'width: ' + progress + '%'"></div>
                                </div>

                                <div class="flex items-center">
                                    @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}"
                                            class="h-10 w-10 object-cover rounded border-2 border-blue-400">
                                        <span class="ml-2 text-[10px] text-blue-600 font-bold uppercase">Foto Baru
                                            Siap</span>
                                    @elseif($foto_existing)
                                        <a href="{{ asset('SDM/inventaris/foto/' . $foto_existing) }}" target="_blank"
                                            class="flex items-center text-[10px] text-gray-500 hover:text-blue-600 transition">
                                            <i class="fas fa-image mr-1"></i> Lihat Foto Saat Ini
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @error('foto')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- DOKUMEN --}}
                        <div x-data="{ isUploadingDoc: false, progressDoc: 0 }" x-on:livewire-upload-start="isUploadingDoc = true"
                            x-on:livewire-upload-finish="isUploadingDoc = false"
                            x-on:livewire-upload-error="isUploadingDoc = false"
                            x-on:livewire-upload-progress="progressDoc = $event.detail.progress">
                            <x-ui.sccr-input type="file" label="Dokumen Pendukung (PDF)" name="dokumen"
                                wire:model="dokumen" accept="application/pdf" />

                            <div class="mt-2 flex items-center space-x-4">
                                <div x-show="isUploadingDoc" class="flex-1 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                        :style="'width: ' + progressDoc + '%'"></div>
                                </div>

                                <div class="flex items-center">
                                    @if ($dokumen)
                                        <span class="text-[10px] text-blue-600 font-bold uppercase">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF Baru Siap
                                        </span>
                                    @elseif($dokumen_existing)
                                        <a href="{{ asset('SDM/inventaris/dokumen/' . $dokumen_existing) }}"
                                            target="_blank"
                                            class="flex items-center text-[10px] text-gray-500 hover:text-red-600 transition">
                                            <i class="fas fa-file-pdf mr-1"></i> Lihat Dokumen Saat Ini
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @error('dokumen')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- STATUS + TGL --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2 border-t">
                        <x-ui.sccr-select name="status" label="Status Kondisi" wire:model="status"
                            :options="[
                                'Baik' => 'Baik',
                                'Rusak' => 'Rusak',
                                'Hilang' => 'Hilang',
                                'Dalam Perbaikan' => 'Dalam Perbaikan',
                            ]" />
                        @error('status')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror

                        <x-ui.sccr-date label="Tanggal Status" name="tanggal_status" wire:model="tanggal_status" />
                        @error('tanggal_status')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end items-center space-x-3 pt-2">
                        <x-ui.sccr-button type="button" wire:click="confirmCancel" variant="secondary">
                            Batal
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="submit" variant="success" class="px-8">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </x-ui.sccr-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KONFIRMASI BATAL --}}
    @if ($showCancelConfirm)
        <x-ui.sccr-modal :show="true" maxWidth="sm">
            <div class="p-6 text-center">
                <h3 class="text-lg font-bold text-gray-800">Batalkan Perubahan?</h3>
                <p class="text-sm text-gray-500 mt-2">
                    Data yang sudah Anda ubah pada label <span class="font-mono font-bold">{{ $kode_label }}</span>
                    tidak akan disimpan.
                </p>

                <div class="mt-6 flex justify-center space-x-3">
                    <x-ui.sccr-button type="button" wire:click="$set('showCancelConfirm', false)"
                        variant="secondary">
                        Lanjutkan Edit
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="cancel" variant="danger">
                        Ya, Batalkan
                    </x-ui.sccr-button>
                </div>
            </div>
        </x-ui.sccr-modal>
    @endif
</div>
