<x-ui.sccr-card transparent wire:key="stock-location" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stok per Lokasi</h1>
                <p class="text-blue-100 text-sm">
                    Informasi stok berdasarkan lokasi
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
                        Lokasi
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Lokasi --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Lokasi</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options"
                        class="w-40" />
                </div>

                {{-- FILTER 2: Kategori --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Kategori</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options"
                        class="w-40" />
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

                            <th wire:click="sortBy('location_name')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Lokasi {!! $sortField === 'location_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('total_items')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Jumlah Total Item  {!! $sortField === 'total_items' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->location_id }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 text-sm font-semibold">
                                    {{ $item->location_name ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-center text-sm font-semibold">
                                    {{ $item->total_items }}
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="openDetail('{{ $item->location_id }}')"
                                        class="text-gray-700 hover:scale-125" title="Detail">
                                        <x-ui.sccr-icon name="eye" :size="20" />
                                    </x-ui.sccr-button>
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

    {{-- ================= OVERLAY: DETAIL ================= --}}
    @if ($overlayMode === 'detail' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-7xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                <div class="p-6 border-b bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-800">Detail Lokasi: {{ $detailData['location']?->name ?? '-' }}</h2>
                    <p class="text-sm text-gray-500">Daftar item dan mutation history</p>
                </div>

                <div class="flex-1 overflow-auto p-6">
                    @forelse ($detailData['items'] as $itemData)
                        @php
                            $balance = $itemData->balance;
                            $minStock = $balance->item?->min_stock ?? 0;
                            $qtyAvailable = $balance->qty_available ?? 0;
                            $isCritical = $qtyAvailable <= $minStock;
                            $isWarning = $qtyAvailable > $minStock && $qtyAvailable <= ($minStock * 1.2);
                        @endphp

                        <div class="mb-6 border rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-100 flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-gray-800">{{ $balance->item?->name ?? '-' }}</span>
                                    <span class="text-gray-500 text-sm ml-2">({{ $balance->item?->sku ?? '-' }})</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $balance->item?->category?->name ?? '-' }}
                                </div>
                            </div>

                            <div class="px-4 py-3 grid grid-cols-4 gap-4 @if($isCritical) bg-red-50 @elseif($isWarning) bg-yellow-50 @else bg-white @endif">
                                <div>
                                    <div class="text-xs text-gray-500 uppercase">Available</div>
                                    <div class="font-mono font-bold @if($isCritical) text-red-600 @elseif($isWarning) text-yellow-600 @else text-gray-800 @endif">
                                        {{ number_format($qtyAvailable, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-400">Min: {{ number_format($minStock, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 uppercase">Reserved</div>
                                    <div class="font-mono font-semibold text-gray-800">
                                        {{ number_format($balance->qty_reserved ?? 0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 uppercase">Waste</div>
                                    <div class="font-mono font-semibold text-gray-800">
                                        {{ number_format($balance->qty_waste ?? 0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 uppercase">Satuan</div>
                                    <div class="font-semibold text-gray-800">
                                        {{ $balance->uom?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>

                            @if($itemData->mutations->count() > 0)
                                <div class="border-t">
                                    <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                        Mutation History
                                    </div>
                                    <div class="max-h-40 overflow-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-100 sticky top-0">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs">Tanggal</th>
                                                    <th class="px-4 py-2 text-left text-xs">Type</th>
                                                    <th class="px-4 py-2 text-right text-xs">Qty</th>
                                                    <th class="px-4 py-2 text-right text-xs">Before</th>
                                                    <th class="px-4 py-2 text-right text-xs">After</th>
                                                    <th class="px-4 py-2 text-left text-xs">Ref</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                @foreach($itemData->mutations as $mutation)
                                                    <tr>
                                                        <td class="px-4 py-1 text-gray-600">
                                                            {{ $mutation->created_at?->format('Y-m-d H:i') ?? '-' }}
                                                        </td>
                                                        <td class="px-4 py-1">
                                                            @php
                                                                $typeColors = [
                                                                    'in' => 'text-green-600',
                                                                    'out' => 'text-red-600',
                                                                    'transfer_in' => 'text-blue-600',
                                                                    'transfer_out' => 'text-orange-600',
                                                                    'adjustment' => 'text-purple-600',
                                                                    'reserve' => 'text-yellow-600',
                                                                    'unreserve' => 'text-gray-600',
                                                                    'consume' => 'text-pink-600',
                                                                    'waste' => 'text-red-800'
                                                                ];
                                                            @endphp
                                                            <span class="{{ $typeColors[$mutation->type] ?? 'text-gray-800' }} font-medium">
                                                                {{ $mutation->type }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-1 text-right font-mono">
                                                            {{ number_format($mutation->qty, 2) }}
                                                        </td>
                                                        <td class="px-4 py-1 text-right font-mono text-gray-500">
                                                            {{ number_format($mutation->qty_before ?? 0, 2) }}
                                                        </td>
                                                        <td class="px-4 py-1 text-right font-mono text-gray-500">
                                                            {{ number_format($mutation->qty_after ?? 0, 2) }}
                                                        </td>
                                                        <td class="px-4 py-1 text-gray-500">
                                                            {{ $mutation->reference_type ?? '-' }} #{{ $mutation->reference_id ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="px-4 py-3 text-sm text-gray-400 italic border-t">
                                    Tidak ada mutation history
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-400 italic">
                            Tidak ada item di lokasi ini
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
