<x-ui.sccr-card transparent wire:key="stock-mutation" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Mutation</h1>
                <p class="text-blue-100 text-sm">
                    Riwayat mutasi stok barang
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
                        Search
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Item/SKU/Location/Reference..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Type Mutasi --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Type</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-44" />
                </div>

                {{-- FILTER 2: Location --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Location</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options" class="w-40" />
                </div>

                {{-- FILTER 3: Item --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Item</span>
                    <x-ui.sccr-select name="filter3" wire:model.live="filter3" :options="$filter3Options" class="w-40" />
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
                            <th class="px-2 py-3 text-center w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th class="px-2 py-3 text-left text-xs font-bold w-12">
                                ID
                            </th>

                            <th wire:click="sortBy('created_at')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Tanggal {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('item_name')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Item {!! $sortField === 'item_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- <th wire:click="sortBy('location_name')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Location {!! $sortField === 'location_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th> --}}

                            <th wire:click="sortBy('type')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Type {!! $sortField === 'type' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('qty')"
                                class="px-3 py-3 text-right text-xs font-bold cursor-pointer">
                                Qty {!! $sortField === 'qty' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-3 py-3 text-right text-xs font-bold">
                                Before
                            </th>

                            <th class="px-3 py-3 text-right text-xs font-bold">
                                After
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Reference
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Dari/Ke
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Notes
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Unit
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            @php
                                $typeColors = [
                                    'in' => 'bg-green-100 text-green-800 border-green-200',
                                    'out' => 'bg-red-100 text-red-800 border-red-200',
                                    'transfer_in' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'transfer_out' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'adjustment' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'reserve' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'unreserve' => 'bg-gray-100 text-gray-800 border-gray-200',
                                    'consume' => 'bg-pink-100 text-pink-800 border-pink-200',
                                    'waste' => 'bg-red-200 text-red-900 border-red-300',
                                ];
                                $typeLabels = [
                                    'in' => 'IN',
                                    'out' => 'OUT',
                                    'transfer_in' => 'TFR IN',
                                    'transfer_out' => 'TFR OUT',
                                    'adjustment' => 'ADJ',
                                    'reserve' => 'RSV',
                                    'unreserve' => 'UNRSV',
                                    'consume' => 'CNSM',
                                    'waste' => 'WSTE',
                                ];
                                $badgeClass = $typeColors[$item->type] ?? 'bg-gray-100 text-gray-800';
                                $typeLabel = $typeLabels[$item->type] ?? $item->type;
                            @endphp
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->id }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                <td class="px-2 py-2 font-mono text-xs font-semibold">
                                    {{ $item->id }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <div class="text-gray-800">{{ $item->created_at?->format('Y-m-d') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->created_at?->format('H:i:s') ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <div class="font-medium">{{ $item->item?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->item?->sku ?? '-' }}</div>
                                </td>

                                {{-- <td class="px-3 py-2 text-sm">
                                    {{ $item->location?->name ?? '-' }}
                                </td> --}}

                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border {{ $badgeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <td class="px-3 py-2 text-right text-sm font-mono font-semibold">
                                    {{ number_format($item->qty, 2) }}
                                </td>

                                <td class="px-3 py-2 text-right text-sm font-mono text-gray-500">
                                    {{ number_format($item->qty_before ?? 0, 2) }}
                                </td>

                                <td class="px-3 py-2 text-right text-sm font-mono text-gray-500">
                                    {{ number_format($item->qty_after ?? 0, 2) }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    @if ($item->reference_type || $item->reference_id)
                                        <span class="text-blue-600">{{ $item->reference_type ?? '-' }}</span>
                                        @if ($item->reference_id)
                                            <span class="text-gray-500">#{{ $item->reference_id }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    @if (in_array($item->type, ['transfer_in', 'transfer_out']))
                                        @if ($item->fromLocation)
                                            <span class="text-orange-600">from: {{ $item->fromLocation->name }}</span>
                                        @endif
                                        @if ($item->toLocation)
                                            <span class="text-blue-600">to: {{ $item->toLocation->name }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-500 max-w-32 truncate">
                                    {{ $item->notes ?? '-' }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $item->uom?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="py-10 text-center text-gray-400 italic">
                                    No data found
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
