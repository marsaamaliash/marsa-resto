<x-ui.sccr-card transparent wire:key="stock-item" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Item</h1>
                <p class="text-blue-100 text-sm">
                    Informasi stok total berdasarkan item
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
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
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

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                ID
                            </th>

                            <th wire:click="sortBy('item_name')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Item {!! $sortField === 'item_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('item_sku')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                SKU {!! $sortField === 'item_sku' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                Kategori
                            </th>

                            <th wire:click="sortBy('total_available')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Qty Available {!! $sortField === 'total_available' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('total_reserved')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Qty Reserved {!! $sortField === 'total_reserved' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('total_in_transit')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Qty In Transit {!! $sortField === 'total_in_transit' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('total_waste')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Qty Waste {!! $sortField === 'total_waste' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>


                            <th class="px-4 py-3 text-right text-xs font-bold">
                                Total Qty
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                Satuan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->item_id }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 font-mono text-sm font-semibold">
                                    {{ $item->item_id }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->item?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-2 font-mono text-sm">
                                    {{ $item->item?->sku ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->item?->category?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono">
                                    {{ number_format($item->total_available, 2) }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono">
                                    {{ number_format($item->total_reserved, 2) }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono">
                                    {{ number_format($item->total_in_transit, 2) }}
                                </td>


                                <td class="px-4 py-2 text-right text-sm font-mono">
                                    {{ number_format($item->total_waste, 2) }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono font-bold">
                                    {{ number_format($item->total_qty, 2) }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->item?->uom?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-gray-400 italic">
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

</x-ui.sccr-card>
