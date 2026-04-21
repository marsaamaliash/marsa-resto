<x-ui.sccr-card transparent wire:key="stock-minimal" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-red-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Kritis</h1>
                <p class="text-red-100 text-sm">
                    Item dengan stok mendekati batas minimal
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Showing <span class="font-bold text-black">{{ $data->total() }}</span> of <span class="font-bold text-black">{{ $totalAll }}</span> data
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
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Type and press enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Category --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Category</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options"
                        class="w-40" />
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="Search" :size="20" />
                        Search
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

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                Category
                            </th>

                            <th wire:click="sortBy('qty_available')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Stok Sekarang {!! $sortField === 'qty_available' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('min_stock')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Min Stok {!! $sortField === 'min_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('selisih')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Selisih {!! $sortField === 'selisih' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Status
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                Unit
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($data as $item)
                            @php
                                $isCritical = $item->status === 'critical';
                                $isWarning = $item->status === 'warning';
                            @endphp
                            <tr class="hover:bg-gray-200 transition @if($isCritical) bg-red-50 @elseif($isWarning) bg-yellow-50 @endif">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->item_id }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 font-mono text-sm font-semibold">
                                    {{ $item->item_id }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    <div class="font-medium">{{ $item->item?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->item?->sku ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->item?->category?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono font-bold @if($isCritical) text-red-600 @elseif($isWarning) text-yellow-600 @endif">
                                    {{ number_format($item->qty_available, 2) }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono">
                                    {{ number_format($item->min_stock, 2) }}
                                </td>

                                <td class="px-4 py-2 text-right text-sm font-mono @if($item->selisih > 0) text-red-600 font-semibold @else text-gray-500 @endif">
                                    {{ number_format($item->selisih, 2) }}
                                </td>

                                <td class="px-4 py-2 text-center">
                                    @if($isCritical)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Kritis
                                        </span>
                                    @elseif($isWarning)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Warning
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item->item?->uom?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-gray-400 italic">
                                    No items with critical stock
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
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> items selected
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
