<x-ui.sccr-card transparent wire:key="item" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Item</h1>
                <p class="text-blue-100 text-sm">
                    Item Bahan
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> dari <span class="font-bold text-black">{{ $totalAll }}</span> data
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
                        Nama / SKU
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Kategori --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Kategori</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options"
                        class="w-40" />
                </div>

                {{-- FILTER 2: Satuan --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Satuan</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options"
                        class="w-40" />
                </div>

                {{-- FILTER 3: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <select wire:model.live="filterStatus" class="border-gray-300 rounded-md text-sm w-32">
                        <option value="">Semua</option>
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

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

                    {{-- COLUMN PICKER --}}
                    <div class="relative">
                        <x-ui.sccr-button type="button" wire:click="toggleColumnPicker" variant="info"
                            class="bg-gray-400 text-gray-900 hover:bg-gray-300">
                            <x-ui.sccr-icon name="columns" :size="20" />
                            Kolom
                        </x-ui.sccr-button>

                        @if ($showColumnPicker)
                            <div class="absolute right-0 top-full mt-1 w-56 bg-white rounded-lg shadow-xl border z-30 p-3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold text-gray-700 uppercase">Tampilkan Kolom</span>
                                    <button type="button" wire:click="resetColumns"
                                        class="text-xs text-blue-600 hover:text-blue-800">Reset</button>
                                </div>
                                <div class="space-y-1 max-h-64 overflow-y-auto">
                                    @foreach ($availableColumns as $col)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input type="checkbox" wire:model.live="columnVisibility.{{ $col['key'] }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-gray-700">{{ $col['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
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
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            {{-- SELECT ALL CHECKBOX --}}
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            @if ($columnVisibility['id'])
                                <th wire:click="sortBy('id')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['name'])
                                <th wire:click="sortBy('name')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Nama {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['sku'])
                                <th wire:click="sortBy('sku')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    SKU {!! $sortField === 'sku' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['category'])
                                <th wire:click="sortBy('category_name')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Kategori {!! $sortField === 'category_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['uom'])
                                <th wire:click="sortBy('uom_name')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Satuan {!! $sortField === 'uom_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['min_stock'])
                                <th wire:click="sortBy('min_stock')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Min. Stok {!! $sortField === 'min_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['is_active'])
                                <th wire:click="sortBy('is_active')"
                                    class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                    Aktif {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['is_stockable'])
                                <th wire:click="sortBy('is_stockable')"
                                    class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                    Stokable {!! $sortField === 'is_stockable' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['type'])
                                <th class="px-4 py-3 text-center text-xs font-bold">Tipe</th>
                            @endif

                            @if ($columnVisibility['has_batch'])
                                <th class="px-4 py-3 text-center text-xs font-bold">Batch</th>
                            @endif

                            @if ($columnVisibility['has_expiry'])
                                <th class="px-4 py-3 text-center text-xs font-bold">Expiry</th>
                            @endif

                            @if ($columnVisibility['status'])
                                <th class="px-4 py-3 text-center text-xs font-bold">Status</th>
                            @endif

                            @if ($columnVisibility['created_at'])
                                <th wire:click="sortBy('created_at')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Dibuat {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['updated_at'])
                                <th wire:click="sortBy('updated_at')"
                                    class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                    Diubah {!! $sortField === 'updated_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            {{-- ACTIONS HEADER --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle"
                                            wire:click="openCreate" class="w-8 h-8 hover:scale-105"
                                            title="Tambah Data">
                                            <x-ui.sccr-icon name="plus" :size="18" />
                                        </x-ui.sccr-button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition {{ $item->deleted_at ? 'bg-red-50' : '' }}">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                @if ($columnVisibility['id'])
                                    <td class="px-4 py-2 font-mono text-sm font-semibold">
                                        {{ $item['id'] }}
                                    </td>
                                @endif

                                @if ($columnVisibility['name'])
                                    <td class="px-4 py-2 text-sm">
                                        {{ $item['name'] }}
                                    </td>
                                @endif

                                @if ($columnVisibility['sku'])
                                    <td class="px-4 py-2 font-mono text-sm">
                                        {{ $item['sku'] }}
                                    </td>
                                @endif

                                @if ($columnVisibility['category'])
                                    <td class="px-4 py-2 text-sm">
                                        {{ $item->category?->name ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['uom'])
                                    <td class="px-4 py-2 text-sm">
                                        {{ $item->uom?->name ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['min_stock'])
                                    <td class="px-4 py-2 text-sm">
                                        {{ $item->is_stockable ? number_format($item['min_stock'], 2) : '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['is_active'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        @if ($item['is_active'])
                                            <span class="text-green-600 font-semibold">Ya</span>
                                        @else
                                            <span class="text-red-600">Tidak</span>
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['is_stockable'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        @if ($item['is_stockable'])
                                            <span class="text-green-600 font-semibold">Ya</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['type'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        @if ($item->type === 'raw')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Raw Material</span>
                                        @elseif ($item->type === 'prep')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Semi Finished</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['has_batch'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        {{ $item->is_stockable ? ($item->has_batch ? 'Ya' : 'Tidak') : '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['has_expiry'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        {{ $item->is_stockable ? ($item->has_expiry ? 'Ya' : 'Tidak') : '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['status'])
                                    <td class="px-4 py-2 text-center text-sm">
                                        @if ($item->deleted_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                                        @elseif (! $item['is_active'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['created_at'])
                                    <td class="px-4 py-2 text-sm text-gray-500">
                                        {{ $item->created_at?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['updated_at'])
                                    <td class="px-4 py-2 text-sm text-gray-500">
                                        {{ $item->updated_at?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                @endif

                                {{-- ROW ACTIONS --}}
                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-3">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $item['id'] }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>

                                        @if ($canUpdate && ! $item->deleted_at)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit('{{ $item['id'] }}')"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canDelete && ! $item->deleted_at)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="deleteItem('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Hapus"
                                                wire:confirm="Yakin ingin menghapus item ini?">
                                                <x-ui.sccr-icon name="trash" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canDelete && $item->deleted_at)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="restoreItem('{{ $item['id'] }}')"
                                                class="text-green-600 hover:scale-125" title="Restore"
                                                wire:confirm="Yakin ingin me-restore item ini?">
                                                <x-ui.sccr-icon name="refresh" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
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
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.master.item.item-create')
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: SHOW ================= --}}
    @if ($overlayMode === 'show' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.master.item.item-show', ['id' => $overlayId], key($overlayId))
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.master.item.item-edit', ['id' => $overlayId], key($overlayId))
            </div>
        </div>
    @endif

</x-ui.sccr-card>
