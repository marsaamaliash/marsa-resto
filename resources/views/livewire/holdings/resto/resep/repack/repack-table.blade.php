<x-ui.sccr-card transparent wire:key="satuan" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Repack Stok</h1>
                <p class="text-blue-100 text-sm">
                    Repack/pecah unit item
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> data
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                {{-- SEARCH INPUT --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        No. Repack
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik no. repack lalu enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1 --}}
                {{-- <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">{{FILTER1_LABEL}}</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options"
                        class="w-40" />
                </div> --}}

                {{-- FILTER 2 --}}
                {{-- <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">{{FILTER2_LABEL}}</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options"
                        class="w-40" />
                </div> --}}

                {{-- ACTION BUTTONS --}}
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

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                        class="bg-gray-600 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />
                        Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                        class="bg-gray-500 text-gray-900 hover:bg-gray-400" :disabled="count($selectedItems) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selectedItems) }})
                    </x-ui.sccr-button>
                </div>
            </form>

            {{-- Right: perpage --}}
            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">
                        Show
                    </span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TABLE (SCROLL AREA) ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-slate-800 text-white sticky top-0 z-10 shadow-md">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th wire:click="sortBy('repack_number')"
                                class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:text-blue-300">
                                No. Repack {!! $sortField === 'repack_number' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-amber-600/50">
                                Konversi
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Item Sumber
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Item Target
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Lokasi
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Dibuat Oleh
                            </th>

                            <th wire:click="sortBy('created_at')"
                                class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:text-blue-300">
                                Tanggal {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                            </th>

                            <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider">
                                Status
                            </th>

                            <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>
                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle"
                                            wire:click="openCreate" class="w-7 h-7 hover:scale-105" title="Tambah Data">
                                            <x-ui.sccr-icon name="plus" :size="16" />
                                        </x-ui.sccr-button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($data as $index => $item)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50/80 transition duration-150">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" value="{{ $item->id }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item->repack_number }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2 text-sm">
                                        <span class="font-mono font-semibold text-gray-700">{{ $item->qty_source_taken }}</span>
                                        <span class="text-xs text-gray-400">{{ $item->sourceItem?->uom?->name ?? 'unit' }}</span>
                                        <span class="inline-flex items-center justify-center w-6 h-5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">×{{ $item->multiplier }}</span>
                                        <span class="text-gray-400">→</span>
                                        <span class="font-mono font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded border border-green-200">{{ $item->qty_target_result }}</span>
                                        <span class="text-xs text-gray-400">{{ $item->targetItem?->uom?->name ?? 'unit' }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-gray-800">{{ $item->sourceItem?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->sourceItem?->uom?->name ?? 'unit' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-gray-800">{{ $item->targetItem?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->targetItem?->uom?->name ?? 'unit' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <x-ui.sccr-icon name="location" :size="12" class="text-gray-400" />
                                        <span class="text-sm text-gray-600">{{ $item->location?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-600">{{ $item->creator?->name ?? 'System' }}</div>
                                </td>

                                <td class="px-3 py-3">
                                    <div class="text-xs text-gray-500">{{ $item->created_at?->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->created_at?->format('H:i') }} WITA</div>
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                        Selesai
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $item['id'] }}')"
                                            class="text-gray-500 hover:text-blue-600 hover:scale-110" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="16" />
                                        </x-ui.sccr-button>

                                        @if ($canUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit('{{ $item['id'] }}')"
                                                class="text-gray-500 hover:text-amber-600 hover:scale-110" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="16" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-gray-400 italic">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-ui.sccr-icon name="inbox" :size="40" />
                                        <span>Data tidak ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MODULE FOOTER (pagination) --}}
            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> item dipilih
                </div>

                <div>
                    {{ $data->links() }}
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
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.resep.repack.repack-create')
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: SHOW ================= --}}
    @if ($overlayMode === 'show' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                {{-- REPACK DETAIL --}}
                <div class="p-6 max-h-[90vh] overflow-y-auto">
                    @if ($detail)
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail Repack Stok</h2>

                        {{-- Header Info --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <div class="font-semibold text-gray-600 text-xs uppercase">No. Repack</div>
                                    <div class="font-mono font-bold text-blue-700 text-lg">{{ $detail['repack_number'] }}</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-600 text-xs uppercase">Lokasi</div>
                                    <div class="font-semibold text-gray-800">{{ $detail->location?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-600 text-xs uppercase">Dibuat Oleh</div>
                                    <div class="text-gray-800">{{ $detail->creator?->name ?? 'System' }}</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-600 text-xs uppercase">Tanggal</div>
                                    <div class="text-gray-800">{{ $detail['created_at']?->format('d/m/Y H:i') }} WITA</div>
                                </div>
                            </div>
                        </div>

                        {{-- ITEM SUMBER - Before & After --}}
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold">OUT</span>
                                <h3 class="font-bold text-lg text-gray-800">Item Sumber (Dikurangi)</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white border border-red-100 rounded-lg p-4">
                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">SEBELUM</div>
                                    <div class="text-xl font-bold text-gray-800">{{ $repackOut?->item?->name ?? $detail->sourceItem?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-600">{{ $repackOut?->uom?->name ?? $detail->sourceItem?->uom?->name ?? 'unit' }}</div>
                                    <div class="mt-2 text-3xl font-mono font-bold text-gray-700">
                                        {{ number_format($repackOut?->qty_before ?? 0, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">di {{ $repackOut?->location?->name ?? $detail->location?->name ?? '-' }}</div>
                                </div>
                                <div class="bg-white border border-red-100 rounded-lg p-4">
                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">SESUDAH</div>
                                    <div class="text-xl font-bold text-gray-800">{{ $repackOut?->item?->name ?? $detail->sourceItem?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-600">{{ $repackOut?->uom?->name ?? $detail->sourceItem?->uom?->name ?? 'unit' }}</div>
                                    <div class="mt-2 text-3xl font-mono font-bold text-red-700">
                                        {{ number_format($repackOut?->qty_after ?? 0, 2) }}
                                        <span class="text-sm font-normal text-gray-500">
                                            ({{ $repackOut?->qty_after < $repackOut?->qty_before ? '-' : '+' }}{{ number_format(($repackOut?->qty_after ?? 0) - ($repackOut?->qty_before ?? 0), 2) }})
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">di {{ $repackOut?->location?->name ?? $detail->location?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-sm text-gray-600">
                                <span class="font-semibold">Qty diambil:</span> 
                                {{ number_format($detail['qty_source_taken'], 2) }} {{ $detail->sourceItem?->uom?->name ?? 'unit' }}
                            </div>
                        </div>

                        {{-- ITEM TARGET - Before & After --}}
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">IN</span>
                                <h3 class="font-bold text-lg text-gray-800">Item Target (Ditambahkan)</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white border border-green-100 rounded-lg p-4">
                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">SEBELUM</div>
                                    <div class="text-xl font-bold text-gray-800">{{ $repackIn?->item?->name ?? $detail->targetItem?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-600">{{ $repackIn?->uom?->name ?? $detail->targetItem?->uom?->name ?? 'unit' }}</div>
                                    <div class="mt-2 text-3xl font-mono font-bold text-gray-700">
                                        {{ number_format($repackIn?->qty_before ?? 0, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">di {{ $repackIn?->location?->name ?? $detail->location?->name ?? '-' }}</div>
                                </div>
                                <div class="bg-white border border-green-100 rounded-lg p-4">
                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">SESUDAH</div>
                                    <div class="text-xl font-bold text-gray-800">{{ $repackIn?->item?->name ?? $detail->targetItem?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-600">{{ $repackIn?->uom?->name ?? $detail->targetItem?->uom?->name ?? 'unit' }}</div>
                                    <div class="mt-2 text-3xl font-mono font-bold text-green-700">
                                        {{ number_format($repackIn?->qty_after ?? 0, 2) }}
                                        <span class="text-sm font-normal text-gray-500">
                                            (+{{ number_format(($repackIn?->qty_after ?? 0) - ($repackIn?->qty_before ?? 0), 2) }})
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">di {{ $repackIn?->location?->name ?? $detail->location?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-sm text-gray-600">
                                <span class="font-semibold">Qty hasil repack:</span> 
                                {{ number_format($detail['qty_source_taken'], 2) }} × {{ $detail['multiplier'] }} = 
                                <span class="font-bold text-green-700">{{ number_format($detail['qty_target_result'], 2) }}</span>
                                {{ $detail->targetItem?->uom?->name ?? 'unit' }}
                            </div>
                        </div>

                        {{-- Notes --}}
                        @if ($detail['notes'])
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold text-gray-600 text-xs uppercase">Catatan</div>
                                <div class="text-sm text-gray-700">{{ $detail['notes'] }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-gray-500 py-12">
                            <p class="text-lg font-semibold">Data tidak ditemukan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                {{-- Replace with actual edit component --}}
                <div class="p-6 text-center text-gray-500">
                    <p class="text-lg font-semibold">Form Edit</p>
                    <p class="text-sm">ID: {{ $overlayId }}</p>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
