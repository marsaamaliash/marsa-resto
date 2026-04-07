<x-ui.sccr-card transparent wire:key="inventaris-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-green-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Inventaris</h1>
                <p class="text-green-100 text-sm">
                    Kelola inventaris, label aset, dan QR Code
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $dataInventaris->total() }}</span> data 📦
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Cari Kode / Nama / Ruangan
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding ⤵️</span>
                    <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="$holdingOptions"
                        class="w-40" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">⬅️ Lokasi</span>
                    <x-ui.sccr-select name="filterLokasi" wire:model.live="filterLokasi" :options="$lokasiOptions"
                        class="w-40" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">⬅️ Ruangan</span>
                    <x-ui.sccr-select name="filterRuangan" wire:model.live="filterRuangan" :options="$ruanganOptions"
                        class="w-40" />
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="printBulk" variant="primary"
                        class="bg-gray-700 text-gray-100 hover:bg-gray-400" :disabled="count($selectedInventaris) === 0">
                        <x-ui.sccr-icon name="printselected" :size="20" />
                        Cetak QR Code ({{ count($selectedInventaris) }})
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                        class="bg-gray-600 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />
                        Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                        class="bg-gray-500 text-gray-900 hover:bg-gray-400" :disabled="count($selectedInventaris) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selectedInventaris) }})
                    </x-ui.sccr-button>

                    @if ($canDelete)
                        <x-ui.sccr-button type="button" wire:click="openDeleteRequestSelected" variant="danger"
                            class="bg-red-600/70 hover:bg-red-700 text-white" title="Ajukan hapus untuk item terpilih"
                            :disabled="count($selectedInventaris) === 0">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="18" />
                                Request Delete
                            </span>
                        </x-ui.sccr-button>
                    @endif
                </div>
            </form>

            {{-- Right: perpage & master --}}
            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">
                        Show 📚
                    </span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                {{-- Master menu --}}
                @if ($canMasterMenu)
                    <div class="relative" x-data="{ openMaster: false }">
                        <x-ui.sccr-button type="button" variant="icon" @click="openMaster = !openMaster"
                            @click.away="openMaster = false"
                            class="w-[38px] h-[38px] bg-gray-900 hover:bg-gray-600 rounded-lg shadow-sm"
                            title="Master Data">
                            <span class="text-[30px]">⚙️</span>
                        </x-ui.sccr-button>

                        <div x-show="openMaster" x-cloak x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute top-0 right-10 mb-2 w-56 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-[110] origin-bottom-right overflow-hidden border border-gray-100">

                            <div class="bg-gray-50 px-4 py-2 border-b">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    Master Data
                                </span>
                            </div>

                            <div class="py-1">
                                @if ($canMasterLokasiCreate)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="$dispatch('inv-master-open', { type: 'lokasi' })"
                                        @click="openMaster = false"
                                        class="group w-full justify-start px-4 py-2.5 rounded-none text-sm text-gray-700 hover:bg-green-600 hover:text-white">
                                        <span class="mr-3">📍</span> Master Lokasi
                                    </x-ui.sccr-button>
                                @endif

                                @if ($canMasterLokasiView)
                                    <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.lokasi.table') }}"
                                        @click="openMaster = false"
                                        class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-green-600 hover:text-white">
                                        <span class="mr-3">📍</span> Kelola Master Lokasi
                                    </a>
                                @endif

                                @if ($canMasterRuanganCreate)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="$dispatch('inv-master-open', { type: 'ruangan' })"
                                        @click="openMaster = false"
                                        class="group w-full justify-start px-4 py-2.5 rounded-none text-sm text-gray-700 hover:bg-green-600 hover:text-white">
                                        <span class="mr-3">🚪</span> Master Ruangan
                                    </x-ui.sccr-button>
                                @endif

                                <div class="border-t border-gray-100 my-1"></div>

                                @if ($canMasterJenisCreate)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="$dispatch('inv-master-open', { type: 'jenis' })"
                                        @click="openMaster = false"
                                        class="group w-full justify-start px-4 py-2.5 rounded-none text-sm text-gray-700 hover:bg-green-600 hover:text-white">
                                        <span class="mr-3">📦</span> Master Jenis Barang
                                    </x-ui.sccr-button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ================= TABLE (SCROLL AREA) ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th wire:click="sortBy('kode_label')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Kode {!! $sortField === 'kode_label' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('nama_barang')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Nama Barang {!! $sortField === 'nama_barang' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('nama_holding')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer select-none">
                                Holding {!! $sortField === 'nama_holding' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('nama_lokasi')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer select-none">
                                Lokasi / Ruangan {!! $sortField === 'nama_lokasi' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('status')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle"
                                            wire:click="openCreate" class="w-8 h-8 hover:scale-105"
                                            title="Tambah Inventaris">
                                            <x-ui.sccr-icon name="plus" :size="18" />
                                        </x-ui.sccr-button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($dataInventaris as $item)
                            <tr class="hover:bg-gray-200 transition">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->kode_label }}"
                                        wire:model.live="selectedInventaris" class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 font-mono text-sm font-semibold">
                                    {{ $item->kode_label }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->nama_barang }}
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $item->holding_alias }}</div>
                                    <div class="text-gray-500">{{ $item->nama_holding }}</div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $item->nama_lokasi }}</div>
                                    <div class="text-gray-500">{{ $item->nama_ruangan }}</div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <x-ui.sccr-badge :type="$item->status_badge_type">
                                        {{ $item->status ?? 'N/A' }}
                                    </x-ui.sccr-badge>
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-3">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $item->kode_label }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>

                                        @if ($canUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit('{{ $item->kode_label }}')"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canDelete)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openDeleteRequestSingle('{{ $item->kode_label }}')"
                                                class="text-red-600 hover:scale-125"
                                                title="Request Delete (Approval)">
                                                <x-ui.sccr-icon name="trash" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- ================= DELETE REQUEST MODAL ================= --}}
                @if ($showConfirmModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Konfirmasi Hapus (Approval)</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Data tidak langsung dihapus. Permintaan akan masuk ke antrian approval
                                        Manager/Head.
                                    </p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDeleteRequest"
                                    class="text-gray-500 hover:text-gray-800" title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div
                                class="mt-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-xs">
                                <div class="font-semibold mb-1">⚠️ Perhatian</div>
                                <ul class="list-disc ml-5 space-y-1">
                                    <li>Status inventaris akan menjadi <b>pending_delete</b> setelah request dikirim.
                                    </li>
                                    <li>Item akan hilang dari daftar aktif sampai approval diputuskan oleh Head /
                                        Manager.</li>
                                </ul>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold text-gray-700">Alasan Hapus</label>
                                <textarea wire:model.live="deleteReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                                    placeholder="Contoh: Barang rusak permanen / salah input / duplikasi data"></textarea>
                                <div class="text-[11px] text-gray-500 mt-1">
                                    Maks 255 karakter.
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-gray-700">
                                @if ($isBulkDelete)
                                    <div>Target: <b>{{ count($selectedInventaris) }}</b> item terpilih</div>
                                @else
                                    <div>Target: <b>{{ $confirmingId }}</b></div>
                                @endif
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary"
                                    wire:click="cancelDeleteRequest">
                                    Batal
                                </x-ui.sccr-button>

                                <x-ui.sccr-button type="button" variant="danger" wire:click="submitDeleteRequest">
                                    Kirim Permintaan Hapus
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- MODULE FOOTER (pagination) FIX di bawah table, bukan ikut scroll --}}
            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedInventaris) }}</span> item dipilih

                    @if ($canDelete && count($selectedInventaris) > 0)
                        <x-ui.sccr-button type="button" variant="danger" wire:click="openDeleteRequestSelected"
                            class="ml-4 h-[30px] px-3 text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="16" />
                                Request Delete Terpilih
                            </span>
                        </x-ui.sccr-button>
                    @endif
                </div>

                <div>
                    {{ $dataInventaris->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= OVERLAY: CREATE ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.inventaris-create :holdingKode="$filterHolding" :lokasiKode="$filterLokasi"
                    :ruanganKode="$filterRuangan"
                    wire:key="overlay-create-{{ $filterHolding }}-{{ $filterLokasi }}-{{ $filterRuangan }}" />
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: SHOW ================= --}}
    @if ($overlayMode === 'show' && $overlayKode)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.inventaris-show :kodeLabel="$overlayKode"
                    wire:key="overlay-show-{{ $overlayKode }}" />
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayKode)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.inventaris-edit :kodeLabel="$overlayKode"
                    wire:key="overlay-edit-{{ $overlayKode }}" />
            </div>
        </div>
    @endif

    {{-- ================= MASTER MODAL (ERP) ================= --}}
    <livewire:holdings.hq.sdm.rt.inventaris.inventaris-master-modal wire:key="inv-master-modal" />

    {{-- Hidden Print Handler --}}
    @script
        <script>
            $wire.on('do-print-bulk', (event) => {
                const win = window.open(event.url, '_blank');
                if (!win) {
                    alert('Mohon izinkan popup browser untuk mencetak label.');
                }
            });
        </script>
    @endscript

</x-ui.sccr-card>
