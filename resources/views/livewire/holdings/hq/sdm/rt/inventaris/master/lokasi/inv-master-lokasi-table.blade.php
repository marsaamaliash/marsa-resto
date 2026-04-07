<x-ui.sccr-card transparent wire:key="inv-master-lokasi-table">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gray-700/80 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Master Lokasi 📚</h1>
                {{-- <p class="text-green-100 text-sm">Truth Master Page — CRUD + Request Delete (Approval)</p> --}}
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $rows->total() }}</span> data
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-8 pb-2">
        <div class="flex flex-wrap items-center justify-between gap-3">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                        Cari Holding / Kode / Nama Lokasi
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">Holding</span>
                    <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="$holdingOptions"
                        class="w-44" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-blue-600/70 text-blue-700 hover:bg-blue-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-600/70 text-gray-700 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                        class="bg-emerald-600/70 hover:bg-emerald-700 text-white">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                        class="bg-blue-600/70 hover:bg-blue-700 text-white" :disabled="count($selected) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selected) }})
                    </x-ui.sccr-button>

                    @if ($canDelete)
                        <x-ui.sccr-button type="button" wire:click="openDeleteRequestSelected" variant="danger"
                            class="bg-red-600/70 hover:bg-red-700 text-white" title="Ajukan hapus untuk item terpilih"
                            :disabled="count($selected) === 0">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="18" />
                                Request Delete ({{ count($selected) }})
                            </span>
                        </x-ui.sccr-button>
                    @endif
                </div>
            </form>

            {{-- Right: perpage --}}
            <div class="flex items-end gap-3 ml-auto">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">Show:</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="mx-6 rounded-xl shadow border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-700/80 text-white">
                <tr>
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                    </th>

                    <th wire:click="sortBy('holding_kode')"
                        class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Holding {!! $sortField === 'holding_kode' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('lokasi_kode')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Kode {!! $sortField === 'lokasi_kode' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('nama_lokasi')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Nama Lokasi {!! $sortField === 'nama_lokasi' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-bold">
                        <div class="flex items-center justify-center gap-2">
                            <span>Aksi</span>

                            @if ($canCreate && $canWrite)
                                <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreate"
                                    class="w-8 h-8 hover:scale-105" title="Tambah Master Lokasi">
                                    <x-ui.sccr-icon name="plus" :size="18" />
                                </x-ui.sccr-button>
                            @endif
                        </div>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-gray-100">
                @forelse ($rows as $r)
                    @php
                        // KEY harus stabil & aman untuk selection + overlay
                        // pakai AB.CD (bukan AB|CD)
                        $key = (string) $r->holding_kode . '.' . (string) $r->lokasi_kode;
                    @endphp

                    <tr class="hover:bg-gray-200 transition">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ $key }}" wire:model.live="selected"
                                class="rounded border-gray-300">
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="font-semibold">{{ $r->holding_kode }}</div>
                            <div class="text-gray-500">{{ $r->nama_holding }}</div>
                        </td>

                        <td class="px-4 py-2 font-mono text-sm font-semibold">
                            {{ $r->lokasi_kode }}
                        </td>

                        <td class="px-4 py-2 text-sm">
                            {{ $r->nama_lokasi }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-3">
                                <x-ui.sccr-button type="button" variant="icon"
                                    wire:click="openShow('{{ $key }}')" class="text-gray-700 hover:scale-125"
                                    title="Detail">
                                    <x-ui.sccr-icon name="eye" :size="20" />
                                </x-ui.sccr-button>

                                @if ($canUpdate)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="openEdit('{{ $key }}')"
                                        class="text-blue-600 hover:scale-125" title="Edit">
                                        <x-ui.sccr-icon name="edit" :size="20" />
                                    </x-ui.sccr-button>
                                @endif

                                @if ($canDelete)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="openDeleteRequestSingle('{{ $key }}')"
                                        class="text-red-600 hover:scale-125" title="Request Delete (Approval)">
                                        <x-ui.sccr-icon name="trash" :size="20" />
                                    </x-ui.sccr-button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400 italic">
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
                                Data tidak langsung dihapus. Permintaan masuk ke antrian approval.
                            </p>
                        </div>

                        <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDeleteRequest"
                            class="text-gray-500 hover:text-gray-800" title="Tutup">
                            <span class="text-xl leading-none">×</span>
                        </x-ui.sccr-button>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-bold text-gray-700">Alasan Hapus</label>
                        <textarea wire:model.live="deleteReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                            placeholder="Contoh: salah input / duplikasi / tidak dipakai"></textarea>
                        <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                    </div>

                    <div class="mt-4 text-xs text-gray-700">
                        @if ($isBulkDelete)
                            <div>Target: <b>{{ count($selected) }}</b> item terpilih</div>
                        @else
                            <div>Target: <b>{{ $confirmingKey }}</b></div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-ui.sccr-button type="button" variant="secondary" wire:click="cancelDeleteRequest">
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

    {{-- ================= FOOTER ================= --}}
    <div class="px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
        <div class="text-sm text-gray-600 flex items-center">
            <span class="font-bold text-gray-800 mr-1">{{ count($selected) }}</span> item dipilih
        </div>

        <div>
            {{ $rows->links() }}
        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= OVERLAY CREATE ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-create
                    wire:key="inv-master-lokasi-create" />
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY SHOW ================= --}}
    @if ($overlayMode === 'show' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-show :rowKey="$overlayKey"
                    wire:key="inv-master-lokasi-show-{{ $overlayKey }}" />
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-edit :rowKey="$overlayKey"
                    wire:key="inv-master-lokasi-edit-{{ $overlayKey }}" />
            </div>
        </div>
    @endif

</x-ui.sccr-card>
